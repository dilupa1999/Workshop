<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $workshop->title }} ({{ $workshop->code }})
            </h2>
            @role('manager')
                <a href="{{ route('workshops.edit', $workshop) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-semibold hover:bg-yellow-600 transition">
                    Edit Workshop
                </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Alert Notifications --}}
            @if(session('success'))
                <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 rounded-md shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-md shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. Workshop Overview Card (Updated to 6 columns to include Location) -->
            <div class="bg-white p-6 rounded-lg shadow grid grid-cols-2 md:grid-cols-6 gap-4">
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Instructor</span>
                    <p class="text-base font-bold text-gray-800">{{ $workshop->instructor }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Campus Location</span>
                    <p class="text-base font-bold text-indigo-700">📍 {{ $workshop->location ?? 'Central Campus' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Date & Time</span>
                    <p class="text-base font-bold text-gray-800">{{ $workshop->date_time->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Total Capacity</span>
                    <p class="text-base font-bold text-gray-800">{{ $workshop->capacity }} seats</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Available Seats</span>
                    <p class="text-base font-bold {{ $workshop->available_seats > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $workshop->available_seats }} seats
                    </p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Waitlist Queue</span>
                    <p class="text-base font-bold text-amber-600">
                        {{ $workshop->waitlist_count ?? $workshop->waitlistedRegistrations()->count() }} waiting
                    </p>
                </div>
            </div>

            <!-- 2. Attendee Registration & Waitlist Form -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $workshop->available_seats > 0 ? 'Register an Attendee' : 'Join Waitlist Queue' }}
                    </h3>
                    @if($workshop->available_seats === 0)
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                            Capacity Reached
                        </span>
                    @endif
                </div>

                {{-- Waitlist Informational Banner when capacity is 0 --}}
                @if($workshop->available_seats === 0)
                    <div class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-md text-sm text-amber-900 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <span class="font-bold">Notice:</span> All {{ $workshop->capacity }} active seats are currently occupied. Registering will place this attendee in the <strong>Waitlist</strong>. 
                            When an active seat is cancelled, waitlisted attendees are automatically promoted.
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('registrations.store', $workshop) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Attendee Name</label>
                        <input type="text" name="attendee_name" value="{{ old('attendee_name') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('attendee_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Attendee Email</label>
                        <input type="email" name="attendee_email" value="{{ old('attendee_email') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('attendee_email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <button type="submit" class="w-full px-4 py-2 font-semibold text-white rounded-md shadow transition duration-150 {{ $workshop->available_seats > 0 ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-amber-600 hover:bg-amber-700' }}">
                            {{ $workshop->available_seats > 0 ? 'Confirm Registration' : 'Add to Waitlist' }}
                        </button>
                    </div>
                </form>
                @error('capacity') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- 3. Registrations, Waitlist & Cancellation History (Includes CSV Export) -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Registration History & Audit Trail</h3>
                    {{-- CSV Export Button for Front Desk & Management --}}
                    <a href="{{ route('workshops.export_attendees', $workshop) }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md shadow transition">
                        📥 Export Attendee List (CSV)
                    </a>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3">Attendee</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Registered By</th>
                            <th class="px-6 py-3">Cancellation Details</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        @forelse($workshop->registrations as $registration)
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900 block">{{ $registration->attendee_name }}</span>
                                    <span class="text-xs text-gray-500">{{ $registration->attendee_email }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($registration->status === 'active')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @elseif($registration->status === 'waitlisted')
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                                            Waitlisted
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-xs">
                                    {{ $registration->registeredByUser->name ?? 'System' }}<br>
                                    <span class="text-gray-400">{{ $registration->created_at->format('Y-m-d h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    @if($registration->status === 'cancelled')
                                        Cancelled by: <strong>{{ $registration->cancelledByUser->name ?? 'Unknown' }}</strong><br>
                                        <span class="text-gray-400">{{ $registration->cancelled_at?->format('Y-m-d h:i A') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($registration->status === 'active')
                                        <form method="POST" action="{{ route('registrations.cancel', $registration) }}" onsubmit="return confirm('Are you sure you want to cancel this registration and free up the seat?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-900 transition">
                                                Cancel Seat
                                            </button>
                                        </form>
                                    @elseif($registration->status === 'waitlisted')
                                        <form method="POST" action="{{ route('registrations.cancel', $registration) }}" onsubmit="return confirm('Remove this attendee from the waitlist?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs font-semibold text-amber-600 hover:text-amber-900 transition">
                                                Leave Waitlist
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-500">No registrations recorded for this workshop yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 4. Workshop Modifications & Audit Log (Changes History) -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Workshop Modifications & Audit Trail</h3>
                        <p class="text-xs text-gray-500">Tracks workshop creation, updates, and configuration edits</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                        {{ $workshop->auditLogs->count() }} Record(s)
                    </span>
                </div>
                <div class="p-6 divide-y divide-gray-100">
                    @forelse($workshop->auditLogs as $log)
                        <div class="py-4 first:pt-0 last:pb-0">
                            <div class="flex justify-between items-center mb-1">
                                <div>
                                    <span class="font-semibold text-gray-800 text-sm">{{ $log->description }}</span>
                                    <span class="text-xs text-gray-500 block">
                                        Performed by: <strong>{{ $log->user->name ?? 'System' }}</strong> on {{ $log->created_at->format('M d, Y h:i A') }}
                                    </span>
                                </div>
                                <span class="px-2 py-1 text-xs font-mono font-semibold rounded bg-gray-100 text-gray-700">
                                    {{ $log->action }}
                                </span>
                            </div>

                            {{-- Changed Attributes (Before vs After) --}}
                            @if(!empty($log->changes['after']))
                                <div class="mt-3 text-xs bg-gray-50 border border-gray-200 p-3 rounded-md font-mono text-gray-700">
                                    <span class="font-bold text-gray-800 uppercase text-[10px] tracking-wider block mb-1">Changed Attributes:</span>
                                    <ul class="space-y-1">
                                        @foreach($log->changes['after'] as $field => $newValue)
                                            <li>
                                                <span class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span> 
                                                <span class="line-through text-red-500 bg-red-50 px-1 rounded">{{ $log->changes['before'][$field] ?? 'null' }}</span> 
                                                &rarr; 
                                                <span class="text-green-600 bg-green-50 px-1 rounded font-bold">{{ $newValue }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-6">No modification records logged for this workshop yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>