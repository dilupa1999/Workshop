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



public function edit(User $user)
{
    $roles = Role::all();
    return view('users.edit', compact('user', 'roles'));
}

public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        'role' => ['required', 'string', 'exists:roles,name'],
    ]);

    $oldRole = $user->roles->pluck('name')->first() ?? 'none';
    $newRole = $validated['role'];

    // Track user basic detail changes
    $user->fill([
        'name' => $validated['name'],
        'email' => $validated['email'],
    ]);
    $dirtyChanges = $user->getDirty();
    $originalValues = array_intersect_key($user->getOriginal(), $dirtyChanges);

    $user->save();

    // Check if role has changed
    $roleChanged = ($oldRole !== $newRole);
    if ($roleChanged) {
        $user->syncRoles([$newRole]);
    }

    // Log to Audit Trail if role or details were changed
    if ($roleChanged || !empty($dirtyChanges)) {
        $beforeChanges = $originalValues;
        $afterChanges = $dirtyChanges;

        if ($roleChanged) {
            $beforeChanges['role'] = $oldRole;
            $afterChanges['role'] = $newRole;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'USER_ROLE_UPDATED',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'description' => "Updated account/role details for '{$user->name}' ({$user->email})",
            'changes' => [
                'before' => $beforeChanges,
                'after' => $afterChanges,
            ],
        ]);
    }

    return redirect()->route('users.index')->with('success', 'User role and account updated successfully.');
}






}
