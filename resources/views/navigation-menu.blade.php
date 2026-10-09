<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    {{-- Admin Only Navigation Link --}}
    @role('admin')
        <x-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">
            {{ __('User Management') }}
        </x-nav-link>
    @endrole

    {{-- Manager & Staff Navigation Link --}}
    @hasanyrole('manager|staff')
        <x-nav-link href="{{ route('workshops.index') }}" :active="request()->routeIs('workshops.*')">
            {{ __('Workshops') }}
        </x-nav-link>
    @endhasanyrole

    {{-- Logout Button (POST Form with CSRF) --}}
    <form method="POST" action="{{ route('logout') }}" class="inline-flex items-center">
        @csrf
        <button type="submit" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-red-600 hover:text-red-800 hover:border-red-300 focus:outline-none transition duration-150 ease-in-out cursor-pointer">
            {{ __('Log Out') }}
        </button>
    </form>
</div>
