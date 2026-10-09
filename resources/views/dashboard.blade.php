<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Welcome, {{ auth()->user()->name }}!</h3>
                <p class="text-gray-600 mb-4">You are currently logged in with the <strong>{{ ucfirst(auth()->user()->roles->first()?->name) }}</strong> role.</p>

                @role('admin')
                    <a href="{{ route('users.index') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700">
                        Go to User Management &rarr;
                    </a>
                @else
                    <a href="{{ route('workshops.index') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700">
                        View Workshops & Registrations &rarr;
                    </a>
                @endrole
            </div>
        </div>
    </div>
</x-app-layout>
