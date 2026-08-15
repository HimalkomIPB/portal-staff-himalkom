<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user()->fresh();

        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan oleh Administrator.',
            ]);
        }

        $userRole = $user->pluckRoleNames();

        $department = $user->department;
        $departmentIsArchived = $department && $department->deleted_at !== null;

        if ($userRole->contains('managing director') || $userRole->contains('bph')) {
            if ($departmentIsArchived) {
                // Logout
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                abort(403, 'Your department is currently archived.');
            }
        }

        $request->session()->regenerate();

        if ($user->isSuperAdmin()) {
            return redirect()->intended($user->getDashboardRoute());
        }

        if ($userRole->contains('supervisor')) {
            return redirect()->intended(route('dashboard.supervisor', absolute: false));
        }

        if ($userRole->contains('managing director') || $userRole->contains('bph') || $userRole->contains('pjs')) {
            if (! $user->department) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Akun ini belum memiliki departemen. Tambahkan Departemen Utama di panel Super Admin.',
                ]);
            }

            return redirect()->intended(route('dashboard', ['department' => $user->department->slug], absolute: false));
        }

        return redirect()->intended();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
