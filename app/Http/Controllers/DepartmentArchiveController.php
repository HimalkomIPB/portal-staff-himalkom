<?php

namespace App\Http\Controllers;

use App\Models\Department;

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
}
