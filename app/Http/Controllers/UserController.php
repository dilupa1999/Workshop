<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\AuditLog;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['manager', 'staff'])->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['manager', 'staff'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::create([
    'user_id' => auth()->id(),
    'action' => 'USER_CREATED',
    'auditable_type' => User::class,
    'auditable_id' => $user->id,
    'description' => "Created staff account for '{$user->name}' with role '{$validated['role']}'",
]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }
}
