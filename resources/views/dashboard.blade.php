<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                
                $totalWorkshops = $stats['total_workshops'] ?? \App\Models\Workshop::count();
                $scheduledWorkshops = $stats['scheduled_workshops'] ?? \App\Models\Workshop::where('status', 'scheduled')->count();
                $activeRegistrations = $stats['active_registrations'] ?? \App\Models\Registration::where('status', 'active')->count();
                $waitlistQueue = $stats['waitlisted_attendees'] ?? \App\Models\Registration::where('status', 'waitlisted')->count();
            @endphp

            <!-- 1. Quick Analytics & Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Card 1: Total Workshops --}}
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Workshops</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold">Catalogue</span>
                    </div>
                    <p class="text-3xl font-black text-gray-900 mt-2">{{ $totalWorkshops }}</p>
                    <span class="text-xs text-gray-400 mt-1 block">Across 3 Campuses</span>
                </div>

                {{-- Card 2: Scheduled Active --}}
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Scheduled Active</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold">Upcoming</span>
                    </div>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ $scheduledWorkshops }}</p>
                    <span class="text-xs text-gray-400 mt-1 block">Open for booking</span>
                </div>

                {{-- Card 3: Confirmed Attendees --}}
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Confirmed Attendees</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-green-50 text-green-700 font-bold">Active</span>
                    </div>
                    <p class="text-3xl font-black text-green-600 mt-2">{{ $activeRegistrations }}</p>
                    <span class="text-xs text-gray-400 mt-1 block">Allocated seats</span>
                </div>

                {{-- Card 4: Waitlist Queue --}}
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-amber-500">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Waitlist Queue</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-bold">FIFO</span>
                    </div>
                    <p class="text-3xl font-black text-amber-600 mt-2">{{ $waitlistQueue }}</p>
                    <span class="text-xs text-gray-400 mt-1 block">Pending auto-promotion</span>
                </div>
            </div>

            <!-- 2. User Welcome & Action Card (Original Layout Preserved) -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Welcome, {{ auth()->user()->name }}!</h3>
                <p class="text-gray-600 mb-4">You are currently logged in with the <strong>{{ ucfirst(auth()->user()->roles->first()?->name) }}</strong> role.</p>

                @role('admin')
                    <a href="{{ route('users.index') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition">
                        Go to User Management &rarr;
                    </a>
                @else
                    <a href="{{ route('workshops.index') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition">
                        View Workshops & Registrations &rarr;
                    </a>
                @endrole
            </div>

        </div>
    </div>
</x-app-layout>