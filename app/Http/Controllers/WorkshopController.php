<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        $query = Workshop::withCount(['activeRegistrations']);

        // 1. Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 2. Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('date_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date_time', '<=', $request->end_date);
        }

        // 3. Filter by Location (Newly Added)
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // 4. Filter by Available Seats
        if ($request->boolean('available_only')) {
            $query->havingRaw('capacity > active_registrations_count');
        }

        $workshops = $query->orderBy('date_time', 'asc')->paginate(15);

        return view('workshops.index', compact('workshops'));
    }

    public function create()
    {
        return view('workshops.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:workshops,code'],
            'title' => ['required', 'string', 'max:255'],
            'instructor' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:100'], // Newly Added
            'date_time' => ['required', 'date', 'after:now'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ]);

        $workshop = Workshop::create($validated);

        // Audit Log entry
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'WORKSHOP_CREATED',
            'auditable_type' => Workshop::class,
            'auditable_id' => $workshop->id,
            'description' => "Created new workshop '{$workshop->title}' ({$workshop->code}) with capacity {$workshop->capacity}",
        ]);

        return redirect()->route('workshops.index')->with('success', 'Workshop created successfully.');
    }

    public function show(Workshop $workshop)
    {
        $workshop->load([
            'registrations.registeredByUser',
            'registrations.cancelledByUser',
            'auditLogs.user'
        ]);

        return view('workshops.show', compact('workshop'));
    }

    public function edit(Workshop $workshop)
    {
        return view('workshops.edit', compact('workshop'));
    }

    public function update(Request $request, Workshop $workshop)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:workshops,code,' . $workshop->id],
            'title' => ['required', 'string', 'max:255'],
            'instructor' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:100'], // Newly Added
            'date_time' => ['required', 'date'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ]);

        // Capture attribute differences for Audit Trail
        $workshop->fill($validated);
        $dirtyChanges = $workshop->getDirty();
        $originalValues = array_intersect_key($workshop->getOriginal(), $dirtyChanges);

        $workshop->save();

        if (!empty($dirtyChanges)) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'WORKSHOP_UPDATED',
                'auditable_type' => Workshop::class,
                'auditable_id' => $workshop->id,
                'description' => "Modified workshop details for '{$workshop->title}'",
                'changes' => [
                    'before' => $originalValues,
                    'after' => $dirtyChanges,
                ],
            ]);
        }

        return redirect()->route('workshops.show', $workshop)->with('success', 'Workshop updated successfully.');
    }


public function exportAttendees(Workshop $workshop)
{
    $fileName = 'attendees-' . \Illuminate\Support\Str::slug($workshop->code) . '-' . now()->format('Ymd_His') . '.csv';

    $registrations = $workshop->registrations()
        ->with(['registeredByUser', 'cancelledByUser'])
        ->orderBy('created_at', 'asc')
        ->get();

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function () use ($registrations, $workshop) {
        $file = fopen('php://output', 'w');
        // CSV Header
        fputcsv($file, ['Workshop Code', 'Workshop Title', 'Attendee Name', 'Attendee Email', 'Status', 'Registered At', 'Registered By', 'Cancellation Details']);

        foreach ($registrations as $reg) {
            fputcsv($file, [
                $workshop->code,
                $workshop->title,
                $reg->attendee_name,
                $reg->attendee_email,
                ucfirst($reg->status),
                $reg->created_at->format('Y-m-d H:i:s'),
                $reg->registeredByUser->name ?? 'System',
                $reg->status === 'cancelled' 
                    ? ('Cancelled by ' . ($reg->cancelledByUser->name ?? 'Unknown') . ' on ' . ($reg->cancelled_at?->format('Y-m-d H:i') ?? 'N/A')) 
                    : 'N/A'
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}




}