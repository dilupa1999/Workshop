<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $workshop->title }} ({{ $workshop->code }})
            </h2>
            @role('manager')
                <a href="{{ route('workshops.edit', $workshop) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-semibold hover:bg-yellow-600">
                    Edit Workshop
                </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
            @endif

            <!-- Workshop Overview Card -->
            <div class="bg-white p-6 rounded-lg shadow grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Instructor</span>
                    <p class="text-base font-bold text-gray-800">{{ $workshop->instructor }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Date & Time</span>
                    <p class="text-base font-bold text-gray-800">{{ $workshop->date_time->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Capacity</span>
                    <p class="text-base font-bold text-gray-800">{{ $workshop->capacity }} seats</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase font-semibold">Seats Remaining</span>
                    <p class="text-base font-bold {{ $workshop->available_seats > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $workshop->available_seats }} seats
                    </p>
                </div>
            </div>

            <!-- Register New Attendee Form -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Register an Attendee</h3>
                @if($workshop->available_seats > 0)
                    <form method="POST" action="{{ route('registrations.store', $workshop) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Attendee Name</label>
                            <input type="text" name="attendee_name" value="{{ old('attendee_name') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('attendee_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Attendee Email</label>
                            <input type="email" name="attendee_email" value="{{ old('attendee_email') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('attendee_email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700">
                                Confirm Registration
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-red-50 text-red-700 rounded-md font-semibold">
                        This workshop has reached maximum capacity. No further registrations can be accepted.
                    </div>
                @endif
                @error('capacity') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- Registrations & Full Audit History -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-bold text-gray-800">Registration History & Audit Trail</h3>
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
                                    <span class="px-2 py-1 text-xs font-bold rounded-full {{ $registration->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($registration->status) }}
                                    </span>
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
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-900">
                                                Cancel Seat
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
        </div>
    </div>
</x-app-layout>
