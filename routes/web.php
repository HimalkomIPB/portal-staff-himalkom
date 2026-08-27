<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentArchiveController;
use App\Http\Controllers\ModViewController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceRequestCommentController;
use App\Http\Controllers\ServiceRequestController;
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
    Route::patch('/dashboard/notifications/read-all', [DashboardController::class, 'markAllAsRead'])
        ->name('dashboard.notifications.markAllAsRead');
});

// -------------------------------------------------------
// Service Requests
// -------------------------------------------------------
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::resource('services', ServiceRequestController::class);
    Route::patch('services/{service}/status', [ServiceRequestController::class, 'updateStatus'])->name('services.status.update');
    Route::patch('services/{service}/assign', [ServiceRequestController::class, 'assign'])->name('services.assign');
    Route::post('services/{service}/upload-final', [ServiceRequestController::class, 'uploadFinal'])->name('services.upload-final');
    Route::patch('services/{service}/approve-final', [ServiceRequestController::class, 'approveFinal'])->name('services.approve-final');
    Route::patch('services/{service}/reject-final', [ServiceRequestController::class, 'rejectFinal'])->name('services.reject-final');
    Route::patch('services/{service}/accept-manager', [ServiceRequestController::class, 'acceptByManager'])->name('services.accept-manager');
    Route::patch('services/{service}/reject-manager', [ServiceRequestController::class, 'rejectByManager'])->name('services.reject-manager');

    Route::post('services/{service}/comments', [ServiceRequestCommentController::class, 'store'])->name('services.comments.store');
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

    // -------------------------------------------------------
    // Calendar / Agenda
    // -------------------------------------------------------

    // Halaman utama kalender — semua role dengan agenda.view
    Route::get('/dashboard/calendar', [AgendaController::class, 'index'])
        ->middleware('permission:agenda.view')
        ->name('dashboard.calendar.index');

    // JSON events untuk Alpine.js
    Route::get('/dashboard/calendar/events', [AgendaController::class, 'events'])
        ->middleware('permission:agenda.view')
        ->name('dashboard.calendar.events');

    // Tambah agenda — hanya MD/PJS/Sekretaris/BPH/Supervisor
    Route::post('/dashboard/calendar', [AgendaController::class, 'store'])
        ->middleware('permission:agenda.create-dept|agenda.create-org')
        ->name('dashboard.calendar.store');

    // Update agenda
    Route::put('/dashboard/calendar/{agenda}', [AgendaController::class, 'update'])
        ->middleware('permission:agenda.edit-dept|agenda.create-org')
        ->name('dashboard.calendar.update');

    // Hapus agenda
    Route::delete('/dashboard/calendar/{agenda}', [AgendaController::class, 'destroy'])
        ->middleware('permission:agenda.delete-dept|agenda.create-org')
        ->name('dashboard.calendar.destroy');

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
