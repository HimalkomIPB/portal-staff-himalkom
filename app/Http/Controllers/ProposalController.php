<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProposalController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $order = "FIELD(status, 'pending', 'approved', 'rejected')";

        if ($user->hasRole('bph')) {
            $proposals = Proposal::with('uploader', 'reviewer')
                ->orderByRaw($order)
                ->orderByDesc('created_at')
                ->paginate(10);
        } else {
            $proposals = Proposal::where('uploader_id', $user->id)
                ->orderByRaw($order)
                ->orderByDesc('created_at')
                ->with('uploader', 'reviewer')
                ->paginate(10);

        }

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
            'file' => 'required|file|mimes:pdf|max:5120',
            ],
            [
                'file.mimes' => 'File harus berformat PDF.',
                'file.max' => 'Ukuran file maksimal 5 MB.',
                'file.required' => 'File proposal wajib diunggah.',
            ]);


        $filepath = $request->file('file')->store('proposals', 'private');

        Proposal::create([
            'uploader_id' => auth()->id(),
            'reviewer_id' => null,
            'title' => Str::title($request->input('nama_proker')),
            'nama_proker' => Str::title($request->input('nama_proker')),
            'file_path' => $filepath,
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard.proposals.index')
            ->with('success', 'Proposal berhasil diupload.');
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

        $proposal->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'],
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('dashboard.proposals.show', $proposal)
            ->with('success', 'Proposal berhasil direview.');
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
