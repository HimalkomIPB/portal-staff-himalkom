<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Department $department): View
    {
        $user = Auth::user();
        $userDepartment = $user->department;

        if (! $department) {
            abort(404, 'Department not found');
        }

        // Hanya izinkan jika itu department utamanya, ATAU dia adalah SC untuk department itu,
        // ATAU dia punya permission untuk view-all (misal Super Admin)
        if (! $user->isSuperAdmin() && $department->id != $user->department_id && ! $user->isSCOf($department) && ! $user->can('performance.view-all')) {
            abort(403, 'Unauthorized access to this department');
        }

        $departmentSlugs = Department::orderBy('name')
            ->pluck('name', 'slug')
            ->toArray();

        $departmentWorkPrograms = $department->workPrograms()
            ->orderBy('name', 'asc')
            ->get();

        return view('dashboard', compact('departmentSlugs', 'department', 'departmentWorkPrograms'));
    }

    public function showNotifications(): View
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->paginate(10);

        return view('dashboard.notifications.index', compact('notifications'));
    }

    public function readNotification($id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function showSupervisor(): View
    {
        $departmentSlugs = Department::orderBy('name')
            ->pluck('name', 'slug')
            ->toArray();

        return view('dashboard-spv', compact('departmentSlugs'));
    }
}
