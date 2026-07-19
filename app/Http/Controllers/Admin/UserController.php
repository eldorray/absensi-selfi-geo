<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * UserController - Manage users and their office assignments.
 */
class UserController extends Controller
{
    /**
     * Human-readable labels for each sync source.
     *
     * @var array<string, string>
     */
    private const SOURCE_LABELS = [
        'guru-mi' => 'Guru MI',
        'guru-smp' => 'Guru SMP',
    ];

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::with(['office', 'role']);

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by office
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $offices = Office::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'offices' => $offices,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $offices = Office::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', [
            'offices' => $offices,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'office_id' => 'nullable|exists:offices,id',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role_id.required' => 'Role wajib dipilih.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'office_id' => $validated['office_id'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Sync users from the data induk API.
     */
    public function syncFromApi(Request $request, UserSyncService $service): RedirectResponse
    {
        $validated = $request->validate([
            'source' => ['required', Rule::in(UserSyncService::SOURCES)],
        ], [
            'source.required' => 'Pilih unit sumber terlebih dahulu.',
            'source.in' => 'Unit sumber tidak valid.',
        ]);

        $source = $validated['source'];
        $unitLabel = self::SOURCE_LABELS[$source] ?? $source;

        try {
            $result = $service->sync($source);
        } catch (ConnectionException $e) {
            Log::error('User sync connection error: '.$e->getMessage());

            return back()->with('error', 'Tidak dapat terhubung ke API data induk.');
        } catch (\RuntimeException $e) {
            Log::error('User sync error: '.$e->getMessage());

            return back()->with('error', $e->getMessage());
        }

        if ($result['failed'] > 0) {
            Log::warning('User sync had row failures', [
                'source' => $source,
                'created' => $result['created'],
                'updated' => $result['updated'],
                'failed' => $result['failed'],
                'errors' => array_slice($result['errors'], 0, 5),
            ]);
        }

        // A run where every row failed is a failure, not a success — surface
        // it as an error with a sample reason so the admin can act on it.
        if ($result['created'] === 0 && $result['updated'] === 0 && $result['failed'] > 0) {
            $sample = $result['errors'][0] ?? '';

            return back()->with(
                'error',
                "Sync {$unitLabel} gagal: semua {$result['failed']} data gagal diproses."
                    .($sample !== '' ? " Contoh: {$sample}" : '')
            );
        }

        $message = "Sync {$unitLabel} selesai: {$result['created']} user baru, {$result['updated']} diperbarui";
        $message .= $result['failed'] > 0 ? ", {$result['failed']} gagal." : '.';

        return back()->with('success', $message);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $offices = Office::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', [
            'user' => $user,
            'offices' => $offices,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'office_id' => $validated['office_id'],
        ]);

        // Update password if provided
        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
