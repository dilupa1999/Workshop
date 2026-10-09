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
        return view('dashboard');
    })->name('dashboard');

    // -------------------------------------------------------------------------
    // 1. ADMIN ONLY: Manage Users & Roles (Workshops are forbidden)
    // -------------------------------------------------------------------------
    Route::middleware(['role:admin'])->prefix('admin')->name('users.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('index');
        Route::get('/users/create', [UserController::class, 'create'])->name('create');
        Route::post('/users', [UserController::class, 'store'])->name('store');
    });

    // -------------------------------------------------------------------------
    // 2. MANAGER & STAFF: View Workshops, Registrations, & Cancel Attendees
    // -------------------------------------------------------------------------
    Route::middleware(['role:manager|staff'])->group(function () {
        Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
        Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');

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
