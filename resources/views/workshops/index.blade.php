<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Workshop Catalogue') }}
            </h2>
            @role('manager')
                <a href="{{ route('workshops.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700">
                    + Add New Workshop
                </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
            @endif

            <!-- Search & Filters -->
            <div class="bg-white p-6 rounded-lg shadow">
                <form method="GET" action="{{ route('workshops.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Status</label>
                        <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                            <option value="">All Statuses</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="available_only" value="1" {{ request('available_only') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span class="ms-2 text-sm text-gray-600">Available Seats Only</span>
                        </label>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold hover:bg-gray-700">Filter</button>
                    </div>
                </form>
            </div>

            <!-- Workshop Cards / Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3">Code & Title</th>
                            <th class="px-6 py-3">Instructor</th>
                            <th class="px-6 py-3">Date & Time</th>
                            <th class="px-6 py-3">Capacity & Seats</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        @forelse($workshops as $workshop)
                            @php
                                $remaining = $workshop->capacity - $workshop->active_registrations_count;
                            @endphp
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900 block">{{ $workshop->title }}</span>
                                    <span class="text-xs text-gray-500 font-mono">{{ $workshop->code }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $workshop->instructor }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $workshop->date_time->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold {{ $remaining > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $remaining }} left
                                    </span>
                                    <span class="text-xs text-gray-500"> / {{ $workshop->capacity }} capacity</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst(str_replace('_', ' ', $workshop->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('workshops.show', $workshop) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">View & Register &rarr;</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No workshops match the selected criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">{{ $workshops->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
