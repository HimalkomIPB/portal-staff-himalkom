<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Department;
use App\Models\WorkProgram;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WorkProgramsController extends Controller
{
    private function canDoAction(Department $department): bool
    {
        if ($this->isCurrentUserBph()) {
            return true;
        }

        return Auth::user()->department_id === $department->id;
    }

    private function isCurrentUserBph(): bool
    {
        return Auth::user()->hasRole('bph');
    }

    private function generateFilename(UploadedFile $file, string $extension = '.pdf')
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // only filename not extension
        $filename = preg_replace('/[^a-zA-Z0-9_\-\s()]/', '', $filename);
        $generatedFilename = time() . '-' . Str::random(rand(4, 16)) . '_' . Str::slug($filename) . $extension;
        return $generatedFilename;
    }

    public function index(Department $department): View
    {
        if (!$this->canDoAction($department)) {
            abort(403, 'Unauthorized Access of Department WorkProgram');
        }

        return view('dashboard.workprograms.index', ['department' => $department]);
    }

    public function detail(Department $department, WorkProgram $workProgram): View
    {
        if (!$this->canDoAction($department)) {
            abort(403, 'Unauthorized Access of Department WorkProgram');
        }

        return view('dashboard.workprograms.detail', ['workProgram' => $workProgram]);
    }

    public function create(Department $department): View
    {
        if (!$this->canDoAction($department)) {
            abort(403, 'Unauthorized Access of Department WorkProgram');
        }

        return view('dashboard.workprograms.create', ['department' => $department]);
    }

    public function store(Request $request, Department $department)
    {
        if (!$this->canDoAction($department)) {
            abort(403, 'Unauthorized Access of Department WorkProgram');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_at' => 'required|date',
            'finished_at' => 'required|date|after_or_equal:start_at',
            'funds' => 'required|numeric|min:0',
            'sources_of_funds' => 'required|array',
            'sources_of_funds.*' => 'string|max:255',
            'participation_total' => 'required|integer|min:0',
            'participation_coverage' => 'required|string|max:255',
            'lpj_url' => 'sometimes|nullable|mimes:pdf|max:5120',
            'spg_url' => 'sometimes|nullable|mimes:pdf|max:5120',
            'proposal_url' => 'sometimes|nullable|mimes:pdf|max:5120',
            'komnews_url' => 'sometimes|nullable|mimes:pdf|max:5120'
        ]);

        DB::beginTransaction();

        try {
            $fileFields = ['lpj_url', 'spg_url', 'proposal_url', 'komnews_url'];

            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $validated[$field] = $request->file($field)->storeAs(
                        'private',
                        $this->generateFilename($request->file($field)),
                        'private'
                    );
                }
            }

            $validated['sources_of_funds'] = json_encode($validated['sources_of_funds']);
            $validated['department_id'] = $department->id;

            $workProgram = WorkProgram::create($validated);
            DB::commit();

            if ($this->isCurrentUserBph()  && Auth::user()->department->id != $department->id) {
                return redirect()->route('dashboard.modview.workprogram.show', ['workProgram' => $workProgram, 'department' => $department])
                    ->with('success', ['message' => "Program kerja untuk $department->name berhasil ditambahkan!", 'id' => Str::ulid()->toBase32()]);
            }

            return redirect()->route('dashboard.workProgram.index', ['department' => $department])
                ->with('success', ['message' => 'Program kerja berhasil ditambahkan!', 'id' => Str::ulid()->toBase32()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dashboard.workProgram.create', ['department' => $department])
                ->withInput()
                ->with('error', ['message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(), 'id' => Str::ulid()->toBase32()]);
        }
    }

    public function edit(Department $department, WorkProgram $workProgram)
    {
        if (!$this->canDoAction($department)) {
            abort(403, 'Unauthorized Access of Department WorkProgram');
        }

        return view('dashboard.workprograms.edit', ['workProgram' => $workProgram]);
    }


    public function update(Request $request, Department $department, WorkProgram $workProgram)
    {
        if (!$this->canDoAction($department)) {
            abort(403, 'Unauthorized Access of Department WorkProgram');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_at' => 'required|date',
            'finished_at' => 'required|date|after_or_equal:start_at',
            'funds' => 'required|numeric|min:0',
            'sources_of_funds' => 'required|array',
            'sources_of_funds.*' => 'string|max:255',
            'participation_total' => 'required|integer|min:0',
            'participation_coverage' => 'required|string|max:255',
            'lpj_url' => 'sometimes|nullable|mimes:pdf|max:5120',
            'spg_url' => 'sometimes|nullable|mimes:pdf|max:5120',
            'proposal_url' => 'sometimes|nullable|mimes:pdf|max:5120',
            'komnews_url' => 'sometimes|nullable|mimes:pdf|max:5120'
        ]);

        DB::beginTransaction();

        try {
            $fileFields = ['lpj_url', 'spg_url', 'proposal_url', 'komnews_url'];

            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $newFile = $request->file($field);
                    $oldFile = $workProgram->$field;

                    if ($oldFile && Storage::disk('private')->exists($oldFile)) {
                        if (md5_file($newFile->path()) !== md5_file(Storage::disk('private')->path($oldFile))) {
                            Storage::disk('private')->delete($oldFile);
                        }
                    }

                    $validated[$field] = $newFile->storeAs(
                        'private',
                        $this->generateFilename($newFile),
                        'private'
                    );
                } else {
                    $validated[$field] = $workProgram->$field;
                }
            }

            $validated['sources_of_funds'] = json_encode($validated['sources_of_funds']);
            $workProgram->update($validated);

            DB::commit();

            if ($this->isCurrentUserBph() && Auth::user()->department->id != $department->id) {
                return redirect()->route('dashboard.modview.workprogram.show', ['workProgram' => $workProgram, 'department' => $department])
                    ->with('success', ['message' => "Program kerja berhasil diperbarui!", 'id' => Str::ulid()->toBase32()]);
            }

            return redirect()->route('dashboard.workProgram.detail', ['workProgram' => $workProgram, 'department' => $department])
                ->with('success', ['message' => 'Program kerja berhasil diperbarui!', 'id' => Str::ulid()->toBase32()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dashboard.workProgram.edit', ['workProgram' => $workProgram, 'department' => $department])
                ->with('error', ['message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage(), 'id' => Str::ulid()->toBase32()]);
        }
    }


    public function destroy(Department $department, WorkProgram $workProgram)
    {
        if (!$this->canDoAction($department)) {
            abort(403, 'Unauthorized Access of Department WorkProgram');
        }
        try {
            DB::beginTransaction();
            $workProgram->delete();
            DB::commit();

            if ($this->isCurrentUserBph()  && Auth::user()->department->id != $department->id) {
                return redirect()->route('dashboard.modview.department.show', ['department' => $department])
                    ->with('success', ['message' => "Program kerja berhasil dihapus!", 'id' => Str::ulid()->toBase32()]);
            }

            return redirect()->route('dashboard.workProgram.index', ['department' => $department])
                ->with('success', ['message' => 'Program kerja berhasil dihapus!', 'id' => Str::ulid()->toBase32()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dashboard.workProgram.index', ['department' => $department])
                ->with('error', ['message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(), 'id' => Str::ulid()->toBase32()]);
        }
    }
}
