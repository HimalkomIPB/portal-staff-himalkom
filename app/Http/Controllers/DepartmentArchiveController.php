<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\WorkProgram;

class DepartmentArchiveController extends Controller
{
    public function index()
    {
        $departments = Department::onlyTrashed()
            ->select('id', 'name', 'description', 'slug', 'deleted_at')
            ->withCount(['workPrograms'])
            ->orderBy('name', 'ASC')
            ->get()
            ->append('managing_director');

        return view('dashboard.archives.index-department', ['departments' => $departments]);
    }

    public function showDepartment(string $id)
    {
        $department = Department::withTrashed()->findOrFail($id);
        if ($department->deleted_at === null) {
            abort(404);
        }

        $department->load(['workPrograms']);
        $department->append('managing_director');

        return view('dashboard.archives.show-department', ['department' => $department]);
    }
    public function showWorkProgram(string $id, string $workProgramId)
    {
        $department = Department::withTrashed()->findOrFail($id);
        if ($department->deleted_at === null) {
            abort(404);
        }

        $workProgram = WorkProgram::where('department_id', $department->id)
            ->with(['comments.author'])
            ->findOrFail($workProgramId);

        return view('dashboard.archives.detail-workprogram', [
            'department' => $department,
            'workProgram' => $workProgram,
        ]);
    }
}
