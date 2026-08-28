<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\WorkProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProposalController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_unless($user->hasRole(['bph', 'superadmin']), 403, 'Hanya BPH yang dapat mengakses menu ini.');

        $status = request('status', 'pending');

        $proposals = Proposal::with('uploader.department', 'reviewer')
            ->when($status !== 'all', function($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.proposals.index', ['proposals' => $proposals]);
    }

    public function create()
    {
        $this->authorize('create', Proposal::class);

        return view('dashboard.proposals.create');
    }

    public function store(Request $request, Proposal $proposal)
    {
        $this->authorize('create', Proposal::class);

        $validated = $request->validate(
            [
            'title' => 'required|string|max:255',
            'nama_proker' => 'required|string|max:255',
            'description' => 'required|string',
            'start_at' => 'required|date',
            'finished_at' => 'required|date|after_or_equal:start_at',
            'funds' => 'required|numeric|min:0',
            'sources_of_funds' => 'required|array',
            'sources_of_funds.*' => 'string|max:255',
            'participation_total' => 'required|integer|min:1',
            'participation_coverage' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:5120',
            ],
            [
                'file.mimes' => 'File harus berformat PDF.',
                'file.max' => 'Ukuran file maksimal 5 MB.',
                'file.required' => 'File proposal wajib diunggah.',
            ]);

        DB::beginTransaction();

        try {
            $filepath = $request->file('file')->store('proposals', 'private');

            $newProposal = Proposal::create([
                'uploader_id' => auth()->id(),
                'reviewer_id' => null,
                'title' => Str::title($request->input('nama_proker')),
                'nama_proker' => Str::title($request->input('nama_proker')),
                'file_path' => $filepath,
                'status' => 'pending',
            ]);

            // Auto-create pending work program
            // encode multiple sources into JSON string for storage
            $validated['sources_of_funds'] = json_encode($validated['sources_of_funds']);
            $workProgram = WorkProgram::create([
                'name' => $validated['nama_proker'],
                'description' => $validated['description'],
                'start_at' => $validated['start_at'],
                'finished_at' => $validated['finished_at'],
                'funds' => $validated['funds'],
                'sources_of_funds' => $validated['sources_of_funds'],
                'participation_total' => $validated['participation_total'],
                'participation_coverage' => $validated['participation_coverage'],
                'department_id' => auth()->user()->department_id,
                'status' => 'pending',
                'proposal_id' => $newProposal->id,
            ]);

            // Notify BPH
            $bphUsers = \App\Models\User::role('bph')->get();
            $user = auth()->user();
            $department = $user->department;
            \Illuminate\Support\Facades\Notification::send($bphUsers, new ProposalNotification(
                'Dokumen Masuk',
                "Proposal baru masuk dari {$user->name} ({$department->name})",
                route('dashboard.proposals.show', $newProposal),
                'proposal_submission'
            ));

            DB::commit();

            return redirect()->route('dashboard.workProgram.index', ['department' => auth()->user()->department])
                ->with('success', ['message' => 'Proposal berhasil diupload. Menunggu review dari BPH.', 'id' => Str::ulid()->toBase32()]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('dashboard.proposals.create')
                ->withInput()
                ->with('error', ['message' => 'Gagal mengupload proposal: ' . $e->getMessage(), 'id' => Str::ulid()->toBase32()]);
        }
    }

    public function show(Proposal $proposal)
    {
        $this->authorize('view', $proposal);

        return view('dashboard.proposals.show', ['proposal' => $proposal->load('uploader', 'reviewer')]);
    }

    public function review(Request $request, Proposal $proposal)
    {
        $this->authorize('review', $proposal);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $proposal->update([
                'status' => $validated['status'],
                'review_notes' => $validated['review_notes'],
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Update related work program status
            $workProgram = $proposal->workProgram;
            if ($workProgram) {
                if ($validated['status'] === 'approved') {
                    $workProgram->update(['status' => 'accepted']);
                } elseif ($validated['status'] === 'rejected') {
                    $workProgram->update(['status' => 'reviewed']);
                }
            }

            // Notify Uploader
            $uploader = $proposal->uploader;
            if ($uploader) {
                $statusText = $validated['status'] === 'approved' ? 'Disetujui' : 'Ditolak/Revisi';
                $notificationUrl = ($validated['status'] === 'approved' && $workProgram)
                    ? route('dashboard.workProgram.detail', ['department' => $uploader->department->slug, 'workProgram' => $workProgram->id])
                    : route('dashboard.workProgram.index', ['department' => $uploader->department->slug]);
                
                $uploader->notify(new \App\Notifications\ProposalNotification(
                    $proposal,
                    'Hasil Review Proposal: ' . $proposal->title,
                    'Proposal Anda telah ' . $statusText . ' oleh BPH.',
                    $notificationUrl
                ));
            }

            DB::commit();

            return redirect()->route('dashboard.proposals.show', $proposal)
                ->with('success', ['message' => 'Proposal berhasil direview.', 'id' => Str::ulid()->toBase32()]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', ['message' => 'Gagal mereview proposal: ' . $e->getMessage(), 'id' => Str::ulid()->toBase32()]);
        }
    }

    public function reupload(Request $request, Proposal $proposal)
    {
        $user = auth()->user();
        abort_unless($user->id === $proposal->uploader_id, 403, 'Hanya pengunggah yang dapat melakukan re-upload proposal ini.');

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5120',
        ], [
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
            'file.required' => 'File revisi wajib diunggah.',
        ]);

        DB::beginTransaction();
        try {
            if (\Illuminate\Support\Facades\Storage::disk('private')->exists($proposal->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($proposal->file_path);
            }

            $filepath = $request->file('file')->store('proposals', 'private');

            $proposal->update([
                'file_path' => $filepath,
                'status' => 'pending',
                'review_notes' => null,
                'reviewer_id' => null,
                'reviewed_at' => null,
            ]);

            if ($proposal->workProgram) {
                $proposal->workProgram->update(['status' => 'pending']);
            }

            $bphUsers = \App\Models\User::role('bph')->get();
            $department = $user->department;
            \Illuminate\Support\Facades\Notification::send($bphUsers, new \App\Notifications\ProposalNotification(
                $proposal,
                'Revisi Dokumen Masuk',
                "Revisi proposal baru masuk dari {$user->name} ({$department->name})",
                route('dashboard.proposals.show', $proposal)
            ));

            DB::commit();
            return redirect()->back()->with('success', ['message' => 'Revisi proposal berhasil diunggah.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', ['message' => 'Gagal mengunggah revisi proposal: ' . $e->getMessage()]);
        }
    }

    public function download(Proposal $proposal)
    {
        $this->authorize('view', $proposal);
        if (! Storage::disk('private')->exists($proposal->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('private')->download($proposal->file_path, $proposal->title.'.pdf');
    }
}
