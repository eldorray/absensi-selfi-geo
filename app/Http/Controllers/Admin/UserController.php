<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Services\UserSyncService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     * Shared default password applied by the admin "reset password" action.
     * The teacher is expected to change it after logging in.
     */
    private const DEFAULT_RESET_PASSWORD = 'Guru12345';

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
     * Export a credential list (name / username / password) for teachers as PDF.
     * Admin-only (whole admin area is behind AdminMiddleware).
     */
    public function exportPasswordsPdf(Request $request): Response
    {
        $officeId = $request->input('office_id');
        $selectedOffice = $officeId ? Office::find((int) $officeId) : null;

        // Honour the same office / role / search filters shown on the list, so
        // the PDF matches what the admin is currently viewing.
        $users = User::with('office')
            ->whereHas('role', fn ($q) => $q->where('is_admin', false))
            ->when($selectedOffice, fn ($q) => $q->where('office_id', $selectedOffice->id))
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->input('role_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('admin.users.passwords-pdf', [
            'users' => $users,
            'selectedOffice' => $selectedOffice,
        ]);

        return $pdf->download('kredensial-guru.pdf');
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
            'visible_password' => $validated['password'],
            'role_id' => $validated['role_id'],
            'office_id' => $validated['office_id'] ?? null,
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

        // Only non-admin accounts (other than this one) can be switch targets.
        $linkableUsers = User::whereKeyNot($user->id)
            ->whereHas('role', fn ($q) => $q->where('is_admin', false))
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', [
            'user' => $user,
            'offices' => $offices,
            'roles' => $roles,
            'linkableUsers' => $linkableUsers,
            'linkedIds' => $user->linkedAccounts()->pluck('users.id')->all(),
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
            'linked_accounts' => ['array'],
            'linked_accounts.*' => [
                'integer',
                Rule::exists('users', 'id')->whereNot('id', $user->id),
                Rule::notIn([$user->id]),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (User::whereKey($value)->whereHas('role', fn ($q) => $q->where('is_admin', true))->exists()) {
                        $fail('Akun admin tidak dapat dijadikan akun terkait.');
                    }
                },
            ],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'office_id' => $validated['office_id'] ?? null,
        ]);

        // Update password if provided
        if (! empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
                'visible_password' => $validated['password'],
            ]);
        }

        $linkedIds = collect((array) ($validated['linked_accounts'] ?? []))
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $previous = array_map('intval', $user->linkedAccounts()->pluck('users.id')->all());
        $user->linkedAccounts()->sync($linkedIds);

        // Mirror the change on the other side so links stay symmetric.
        foreach (array_diff($linkedIds, $previous) as $id) {
            User::find($id)?->linkedAccounts()->syncWithoutDetaching([$user->id]);
        }
        foreach (array_diff($previous, $linkedIds) as $id) {
            User::find($id)?->linkedAccounts()->detach($user->id);
        }

        return redirect()
            ->route('admin.users.index', $request->query())
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Reset a user's password to the shared default ("Guru12345"). The visible
     * copy follows automatically. The teacher should change it after logging in.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $user->update([
            'password' => Hash::make(self::DEFAULT_RESET_PASSWORD),
            'visible_password' => self::DEFAULT_RESET_PASSWORD,
        ]);

        return back()->with('success', 'Password '.$user->name.' direset ke "'.self::DEFAULT_RESET_PASSWORD.'".');
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

        // back() returns to the same paginated/filtered list the delete was
        // triggered from, instead of jumping to page 1.
        return back()->with('success', 'User berhasil dihapus.');
    }
}
