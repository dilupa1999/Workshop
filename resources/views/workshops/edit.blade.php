<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Workshop') }}: {{ $workshop->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <form method="POST" action="{{ route('workshops.update', $workshop) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Workshop Code</label>
                        <input type="text" name="code" value="{{ old('code', $workshop->code) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" value="{{ old('title', $workshop->title) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Instructor</label>
                        <input type="text" name="instructor" value="{{ old('instructor', $workshop->instructor) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('instructor') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Location Dropdown (Pre-selects current workshop location) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Location (Branch / Centre)</label>
                        <select name="location" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach(['Central Campus', 'North Branch', 'South Centre'] as $loc)
                                <option value="{{ $loc }}" {{ old('location', $workshop->location ?? '') == $loc ? 'selected' : '' }}>
                                    {{ $loc }}
                                </option>
                            @endforeach
                        </select>
                        @error('location') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Date & Time</label>
                        <input type="datetime-local" name="date_time" value="{{ old('date_time', $workshop->date_time->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('date_time') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Capacity</label>
                        <input type="number" min="1" name="capacity" value="{{ old('capacity', $workshop->capacity) }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('capacity') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300">
                            @foreach(['scheduled', 'in_progress', 'completed', 'cancelled'] as $st)
                                <option value="{{ $st }}" {{ old('status', $workshop->status) == $st ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $st)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('workshops.show', $workshop) }}" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700">Update Workshop</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>