<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\WorkProgram;

class ModViewController extends Controller
{
    public function index()
    {
        $departments = Department::select('id', 'name', 'description', 'slug')
            ->withCount(['workPrograms'])
            ->orderBy('name', 'ASC')
            ->get()
            ->append('managing_director')
            ->append('pjs_count');

        return view('dashboard.modview.index-department', [
            'departments' => $departments,
        ]);
    }

    public function showDepartment(Department $department)
    {
        $department->load(['workPrograms']);
        $department->append('managing_director');

        return view('dashboard.modview.show-department', [
            'department' => $department,
        ]);
    }

    public function showWorkProgram(Department $department, WorkProgram $workProgram)
    {
        return view('dashboard.modview.show-workprogram', [
            'workProgram' => $workProgram,
        ]);
    }
}
