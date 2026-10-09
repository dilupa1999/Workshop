<?php
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

  Route::get('/dashboard', function () {
    $stats = [
        'total_workshops'      => \App\Models\Workshop::count(),
        'scheduled_workshops'  => \App\Models\Workshop::where('status', 'scheduled')->count(),
        'active_registrations' => \App\Models\Registration::where('status', 'active')->count(),
        'waitlisted_attendees' => \App\Models\Registration::where('status', 'waitlisted')->count(),
    ];

    return view('dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');
    // -------------------------------------------------------------------------
    // 1. ADMIN ONLY: Manage Users & Roles (Workshops are forbidden)
    // -------------------------------------------------------------------------
   Route::middleware(['role:admin'])->prefix('admin')->name('users.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('index');
    Route::get('/users/create', [UserController::class, 'create'])->name('create');
    Route::post('/users', [UserController::class, 'store'])->name('store');
    
    // Edit & Update routes for Admin
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('update');
});

    // -------------------------------------------------------------------------
    // 2. MANAGER & STAFF: View Workshops, Registrations, & Cancel Attendees
    // -------------------------------------------------------------------------
    Route::middleware(['role:manager|staff'])->group(function () {
        Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
        Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');
        Route::get('/workshops/{workshop}/export-attendees', [WorkshopController::class, 'exportAttendees'])->name('workshops.export_attendees');

        // Attendee Registration & Cancellation
        Route::post('/workshops/{workshop}/registrations', [RegistrationController::class, 'store'])
            ->name('registrations.store');
        Route::patch('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])
            ->name('registrations.cancel');
    });

    // -------------------------------------------------------------------------
    // 3. MANAGER ONLY: Add & Edit Workshops
    // -------------------------------------------------------------------------
    Route::middleware(['role:manager'])->group(function () {
        Route::get('/workshops-create', [WorkshopController::class, 'create'])->name('workshops.create');
        Route::post('/workshops', [WorkshopController::class, 'store'])->name('workshops.store');
        Route::get('/workshops/{workshop}/edit', [WorkshopController::class, 'edit'])->name('workshops.edit');
        Route::put('/workshops/{workshop}', [WorkshopController::class, 'update'])->name('workshops.update');
    });
});
