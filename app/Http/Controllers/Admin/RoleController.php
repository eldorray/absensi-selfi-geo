<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * RoleController - Admin CRUD for managing user roles.
 */
class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('admin.roles.index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'is_admin' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_admin'] = $request->boolean('is_admin');

        Role::create($validated);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role,
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
            'is_admin' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_admin'] = $request->boolean('is_admin');

        $role->update($validated);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui!');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deletion if role has users
        if ($role->users()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Role tidak dapat dihapus karena masih memiliki ' . $role->users()->count() . ' pengguna.',
            ]);
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus!');
    }
}
