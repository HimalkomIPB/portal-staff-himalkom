<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentArchiveController;
use App\Http\Controllers\ModViewController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkProgramCommentController;
use App\Http\Controllers\WorkProgramsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// -------------------------------------------------------
// Notification dashboard
// -------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/notifications', [DashboardController::class, 'showNotifications'])
        ->name('dashboard.notifications.index');
    Route::patch('/dashboard/notifications/{id}/read', [DashboardController::class, 'readNotification'])
        ->name('dashboard.notifications.markAsRead');
});

// -------------------------------------------------------
// Main dashboard — semua role yang punya akun dept bisa masuk,
// pembatasan akses per fitur dilakukan di dalam controller / view
// -------------------------------------------------------
Route::middleware('auth')->group(function () {
    // Legacy supervisor (akan dihapus setelah migrasi role selesai)
    Route::get('/dashboard/supervisor', [DashboardController::class, 'showSupervisor'])
        ->middleware('role:supervisor')
        ->name('dashboard.supervisor');

    Route::get('/dashboard/performance', [PerformanceController::class, 'index'])
        ->middleware('permission:performance.view|performance.view-all|performance.evaluate|performance.view-self')
        ->name('dashboard.performance.index');

    Route::post('/dashboard/performance', [PerformanceController::class, 'store'])
        ->middleware('permission:performance.evaluate')
        ->name('dashboard.performance.store');

    Route::get('/dashboard/performance/export', [PerformanceController::class, 'export'])
        ->middleware('permission:performance.evaluate|performance.view-all')
        ->name('dashboard.performance.export');

    Route::get('/dashboard/performance/{evaluated}/detail', [PerformanceController::class, 'show'])
        ->middleware('permission:performance.view|performance.view-all|performance.evaluate|performance.view-self')
        ->name('dashboard.performance.show');

    Route::get('/dashboard/{department:slug}', [DashboardController::class, 'show'])
        ->middleware('auth')   // cukup auth, pembatasan dept dilakukan di controller
        ->name('dashboard');
});

// -------------------------------------------------------
// Archives — butuh permission archive.view
// -------------------------------------------------------
Route::middleware(['auth', 'permission:archive.view'])
    ->prefix('/dashboard/archive')
    ->name('dashboard.archive.')
    ->group(function () {
        Route::get('/departments', [DepartmentArchiveController::class, 'index'])
            ->defaults('withTrashed', true)
            ->name('department.index');
        Route::get('/departments/{id}', [DepartmentArchiveController::class, 'showDepartment'])
            ->defaults('withTrashed', true)
            ->name('department.show');
        Route::get('/departments/{id}/workprograms/{workProgramId}', [DepartmentArchiveController::class, 'showWorkProgram'])
            ->defaults('withTrashed', true)
            ->name('workprogram.show');
    });

// -------------------------------------------------------
// Work Programs — permission-based
// -------------------------------------------------------
Route::middleware('auth')
    ->prefix('/dashboard/{department:slug}/workprograms')
    ->name('dashboard.')
    ->group(function () {

        // View — semua yang punya permission work-program.view
        Route::get('/', [WorkProgramsController::class, 'index'])
            ->middleware('permission:work-program.view')
            ->name('workProgram.index');

        // Create — hanya yang punya permission work-program.create
        Route::get('/create', [WorkProgramsController::class, 'create'])
            ->middleware('permission:work-program.create')
            ->name('workProgram.create');

        // Detail — view permission
        Route::get('/{workProgram}', [WorkProgramsController::class, 'detail'])
            ->middleware('permission:work-program.view')
            ->name('workProgram.detail');

        // Edit — edit permission
        Route::get('/{workProgram}/edit', [WorkProgramsController::class, 'edit'])
            ->middleware('permission:work-program.edit')
            ->name('workProgram.edit');

        // Store — create permission
        Route::post('/', [WorkProgramsController::class, 'store'])
            ->middleware('permission:work-program.create')
            ->name('workProgram.store');

        // Update — edit permission
        Route::put('/{workProgram}', [WorkProgramsController::class, 'update'])
            ->middleware('permission:work-program.edit')
            ->name('workProgram.update');

        // Delete — delete permission
        Route::delete('/{workProgram}', [WorkProgramsController::class, 'destroy'])
            ->middleware('permission:work-program.delete')
            ->name('workProgram.destroy');
    });

// -------------------------------------------------------
// Work Program Comments — permission work-program.comment
// -------------------------------------------------------
Route::middleware(['auth', 'permission:work-program.comment'])->group(function () {
    Route::prefix('/dashboard/{workProgram}/comments')
        ->name('dashboard.workProgram.')
        ->group(function () {
            Route::post('/', [WorkProgramCommentController::class, 'store'])->name('comment.store');
            Route::delete('/{comment}', [WorkProgramCommentController::class, 'destroy'])->name('comment.destroy');
        });
});

// -------------------------------------------------------
// Serving Private PDFs
// -------------------------------------------------------
Route::get('/pdf/{filename}', [PDFController::class, 'showPrivatePdf'])
    ->name('pdf.show'); // auth middleware is in the controller itself

// -------------------------------------------------------
// Supervisor ModView (BPH/SC view semua dept)
// Pakai permission archive.view-all untuk BPH
// -------------------------------------------------------
Route::middleware(['auth', 'permission:archive.view-all'])
    ->prefix('/dashboard/mod-view')
    ->name('dashboard.modview.')
    ->group(function () {
        Route::get('/departments', [ModViewController::class, 'index'])
            ->name('department.index');

        Route::get('/{department:slug}', [ModViewController::class, 'showDepartment'])
            ->name('department.show');

        Route::get('/{department:slug}/workprograms/{workProgram}', [ModViewController::class, 'showWorkProgram'])
            ->name('workprogram.show');
    });

// -------------------------------------------------------
// Clear Session
// -------------------------------------------------------
Route::get('/session/clear/{key}', function ($key) {
    session()->forget($key);

    return response()->noContent();
})->name('session.clear');

// -------------------------------------------------------
// Profile (Breeze)
// -------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
