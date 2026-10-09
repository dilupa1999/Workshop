<?php

namespace App\Http\Controllers;

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

        // 3. Filter by Available Seats
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
            'date_time' => ['required', 'date', 'after:now'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ]);

        Workshop::create($validated);

        return redirect()->route('workshops.index')->with('success', 'Workshop created successfully.');
    }

    public function show(Workshop $workshop)
    {
        $workshop->load(['registrations.registeredByUser', 'registrations.cancelledByUser']);
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
            'date_time' => ['required', 'date'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ]);

        $workshop->update($validated);

        return redirect()->route('workshops.show', $workshop)->with('success', 'Workshop updated successfully.');
    }
}
