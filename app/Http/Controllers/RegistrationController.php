<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    /**
     * Register attendee with pessimistic locking to prevent overbooking.
     */
    public function store(Request $request, Workshop $workshop)
    {
        $validated = $request->validate([
            'attendee_name' => ['required', 'string', 'max:255'],
            'attendee_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($validated, $workshop) {
                // Lock the workshop row to handle concurrent submissions
                $lockedWorkshop = Workshop::where('id', $workshop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Recalculate active registrations inside the transaction
                $currentActiveCount = Registration::where('workshop_id', $lockedWorkshop->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->count();

                if ($currentActiveCount >= $lockedWorkshop->capacity) {
                    throw ValidationException::withMessages([
                        'capacity' => 'Registration failed: This workshop is completely full.',
                    ]);
                }

                // Prevent duplicate active registration for the same email
                $existingActive = Registration::where('workshop_id', $lockedWorkshop->id)
                    ->where('attendee_email', $validated['attendee_email'])
                    ->where('status', 'active')
                    ->exists();

                if ($existingActive) {
                    throw ValidationException::withMessages([
                        'attendee_email' => 'This attendee is already actively registered for this workshop.',
                    ]);
                }

                // Record registration with audit metadata
                Registration::create([
                    'workshop_id' => $lockedWorkshop->id,
                    'attendee_name' => $validated['attendee_name'],
                    'attendee_email' => $validated['attendee_email'],
                    'status' => 'active',
                    'registered_by' => Auth::id(),
                ]);
            });

            return redirect()->route('workshops.show', $workshop)->with('success', 'Attendee registered successfully.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Cancel an attendee registration without deleting the record.
     */
    public function cancel(Registration $registration)
    {
        if ($registration->status === 'cancelled') {
            return back()->with('error', 'This registration is already cancelled.');
        }

        $registration->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Registration cancelled. The seat has been freed.');
    }
}
