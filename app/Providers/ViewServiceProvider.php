<?php

namespace App\Providers;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $unreadCount = 0;
            $pendingProposalsCount = 0;
            
            if (Auth::check()) {
                $unreadCount = Auth::user()->unreadNotifications()->count();
                
                // Count pending proposals for BPH users
                if (Auth::user()->hasRole('bph')) {
                    $pendingProposalsCount = Proposal::where('status', 'pending')->count();
                }
            }
            
            $view->with('unreadNotificationsCount', $unreadCount)
                 ->with('pendingProposalsCount', $pendingProposalsCount);
        });
    }
}
