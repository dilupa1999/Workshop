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
    
    public function store(Request $request, Workshop $workshop)
    {
        $validated = $request->validate([
            'attendee_name' => ['required', 'string', 'max:255'],
            'attendee_email' => ['required', 'email', 'max:255'],
        ]);

        $message = '';

        try {
            DB::transaction(function () use ($validated, $workshop, &$message) {
                // Concurrency lock on workshop row
                $lockedWorkshop = Workshop::where('id', $workshop->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check active count
                $currentActiveCount = Registration::where('workshop_id', $lockedWorkshop->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->count();

                // Prevent duplicate active or waitlisted registrations for same email
                $existing = Registration::where('workshop_id', $lockedWorkshop->id)
                    ->where('attendee_email', $validated['attendee_email'])
                    ->whereIn('status', ['active', 'waitlisted'])
                    ->exists();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'attendee_email' => 'This attendee already holds an active or waitlisted spot for this workshop.',
                    ]);
                }

                // If seats are available -> Active Registration
                if ($currentActiveCount < $lockedWorkshop->capacity) {
                    Registration::create([
                        'workshop_id' => $lockedWorkshop->id,
                        'attendee_name' => $validated['attendee_name'],
                        'attendee_email' => $validated['attendee_email'],
                        'status' => 'active',
                        'registered_by' => Auth::id(),
                    ]);
                    $message = 'Attendee registered successfully!';
                } else {
                    // Capacity Full -> Add to Waitlist Queue
                    $waitlistPosition = Registration::where('workshop_id', $lockedWorkshop->id)
                        ->where('status', 'waitlisted')
                        ->count() + 1;

                    Registration::create([
                        'workshop_id' => $lockedWorkshop->id,
                        'attendee_name' => $validated['attendee_name'],
                        'attendee_email' => $validated['attendee_email'],
                        'status' => 'waitlisted',
                        'registered_by' => Auth::id(),
                    ]);
                    $message = "Workshop is at full capacity. Attendee added to Waitlist at position #{$waitlistPosition}.";
                }
            });

            return redirect()->route('workshops.show', $workshop)->with('success', $message);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Cancel an active registration and automatically promote the next waitlisted attendee.
     */
    public function cancel(Registration $registration)
    {
        if ($registration->status === 'cancelled') {
            return back()->with('error', 'This registration is already cancelled.');
        }

        $promotedAttendee = null;

        DB::transaction(function () use ($registration, &$promotedAttendee) {
            $workshopId = $registration->workshop_id;

            // 1. Cancel active or waitlisted seat
            $wasActive = ($registration->status === 'active');
            $registration->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            // 2. If an active seat was freed, check waitlist queue
            if ($wasActive) {
                $nextInQueue = Registration::where('workshop_id', $workshopId)
                    ->where('status', 'waitlisted')
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->first();

                if ($nextInQueue) {
                    $nextInQueue->update([
                        'status' => 'active',
                    ]);
                    $promotedAttendee = $nextInQueue->attendee_name;
                }
            }
        });

        $successMsg = 'Registration cancelled. The seat has been freed.';
        if ($promotedAttendee) {
            $successMsg .= " Waitlisted attendee [{$promotedAttendee}] was automatically moved up to an Active Seat!";
        }

        return back()->with('success', $successMsg);
    }
}
