<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\WorkProgram;

class DepartmentArchiveController extends Controller
{
    public function index()
    {
        $departments = Department::onlyTrashed()
            ->select('id', 'name', 'description', 'slug', 'created_at', 'deleted_at')
            ->withCount(['workPrograms'])
            ->orderBy('created_at', 'DESC')
            ->orderBy('name', 'ASC')
            ->get()
            ->append('managing_director');

        // Group by cabinet year (e.g., 2025 -> "2025/2026")
        $groupedDepartments = $departments->groupBy(function ($department) {
            $year = $department->created_at->year;

            return $year.'/'.($year + 1);
        });

        return view('dashboard.archives.index-department', ['groupedDepartments' => $groupedDepartments]);
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
