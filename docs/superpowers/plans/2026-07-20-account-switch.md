# Ganti Akun Cepat — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a teacher with two separate accounts (e.g. MI & SMP) switch between admin-linked accounts without re-entering a password, with a server-authoritative guard and an admin-viewable audit trail.

**Architecture:** A symmetric self-referencing pivot (`account_links`) records which accounts may switch to each other; the admin manages links on the user edit page. A `POST account.switch` route re-authenticates to a linked, non-admin target after verifying the link server-side, regenerating the session and writing an `account_switch_logs` row. An admin read-only page lists the log.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Tailwind, Pest (sqlite `:memory:`).

## Global Constraints

- PHP files start with `declare(strict_types=1);` and type every parameter/return.
- Explicit `$fillable`; never `$guarded = []` or `Model::create($request->all())`.
- All input validated via `$request->validate()`; authorization enforced server-side, never UI-only.
- Switch is **password-less** but only between **admin-linked** accounts, and **never** into an admin account.
- Format with `./vendor/bin/pint` (changed files only). No new `./vendor/bin/phpstan analyse` errors vs baseline.
- Run `php artisan test` before claiming done.
- Non-admin hitting an `admin` route is redirected to `route('attendance.dashboard')` (302), not 403 — assert accordingly.
- Commands run with `/opt/homebrew/bin/npm` / `php artisan` directly (broken npm shim). Blade-only changes need no build.

---

### Task 1: `account_links` pivot + `User::linkedAccounts()`

**Files:**
- Create: `database/migrations/2026_07_20_000001_create_account_links_table.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/Admin/AccountLinkTest.php`

**Interfaces:**
- Produces: `User::linkedAccounts(): BelongsToMany` (table `account_links`, FK `user_id`, related key `linked_user_id`).

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Admin/AccountLinkTest.php

use App\Models\Role;
use App\Models\User;

function linkGuru(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

test('a user exposes its linked accounts through the pivot', function () {
    $mi = linkGuru('Guru MI');
    $smp = linkGuru('Guru SMP');

    $mi->linkedAccounts()->attach($smp->id);

    expect($mi->fresh()->linkedAccounts->pluck('id')->all())->toBe([$smp->id]);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/AccountLinkTest.php`
Expected: FAIL — `Call to undefined method App\Models\User::linkedAccounts()`.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_20_000001_create_account_links_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linked_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'linked_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_links');
    }
};
```

- [ ] **Step 4: Add the relation to `app/Models/User.php`**

Add `use Illuminate\Database\Eloquent\Relations\BelongsToMany;` to the imports, then add this method after `workSchedules()`:

```php
    /**
     * Accounts this user may switch to (admin-linked, symmetric).
     */
    public function linkedAccounts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'account_links', 'user_id', 'linked_user_id');
    }
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test tests/Feature/Admin/AccountLinkTest.php`
Expected: PASS.

- [ ] **Step 6: Format & commit**

```bash
./vendor/bin/pint app/Models/User.php tests/Feature/Admin/AccountLinkTest.php
git add database/migrations/2026_07_20_000001_create_account_links_table.php app/Models/User.php tests/Feature/Admin/AccountLinkTest.php
git commit -m "feat: add account_links pivot and User::linkedAccounts relation"
```

---

### Task 2: Admin links accounts on the user edit page

**Files:**
- Modify: `app/Http/Controllers/Admin/UserController.php` (`edit`, `update`)
- Modify: `resources/views/admin/users/edit.blade.php`
- Test: `tests/Feature/Admin/AccountLinkTest.php` (append)

**Interfaces:**
- Consumes: `User::linkedAccounts()` (Task 1).
- Produces: two-way link sync on `admin.users.update`; `edit` passes `$linkableUsers` (non-admin, excluding the edited user) and `$linkedIds`.

- [ ] **Step 1: Write the failing tests (append to `tests/Feature/Admin/AccountLinkTest.php`)**

```php
use Illuminate\Support\Facades\Hash;

function linkAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('admin links two non-admin accounts symmetrically', function () {
    $mi = linkGuru('Guru MI');
    $smp = linkGuru('Guru SMP');

    $this->actingAs(linkAdmin())
        ->put(route('admin.users.update', $mi), [
            'name' => $mi->name,
            'email' => $mi->email,
            'role_id' => $mi->role_id,
            'linked_accounts' => [$smp->id],
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($mi->fresh()->linkedAccounts->pluck('id')->all())->toBe([$smp->id])
        ->and($smp->fresh()->linkedAccounts->pluck('id')->all())->toBe([$mi->id]);
});

test('unchecking a link removes it on both sides', function () {
    $mi = linkGuru('Guru MI');
    $smp = linkGuru('Guru SMP');
    $mi->linkedAccounts()->attach($smp->id);
    $smp->linkedAccounts()->attach($mi->id);

    $this->actingAs(linkAdmin())
        ->put(route('admin.users.update', $mi), [
            'name' => $mi->name,
            'email' => $mi->email,
            'role_id' => $mi->role_id,
            'linked_accounts' => [],
        ]);

    expect($mi->fresh()->linkedAccounts)->toHaveCount(0)
        ->and($smp->fresh()->linkedAccounts)->toHaveCount(0);
});

test('admin accounts cannot be linked as a switch target', function () {
    $mi = linkGuru('Guru MI');
    $adminTarget = linkAdmin();

    $this->actingAs(linkAdmin())
        ->put(route('admin.users.update', $mi), [
            'name' => $mi->name,
            'email' => $mi->email,
            'role_id' => $mi->role_id,
            'linked_accounts' => [$adminTarget->id],
        ])
        ->assertSessionHasErrors('linked_accounts.0');

    expect($mi->fresh()->linkedAccounts)->toHaveCount(0);
});
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test tests/Feature/Admin/AccountLinkTest.php`
Expected: FAIL — links not persisted / no validation error yet.

- [ ] **Step 3: Update `edit()` in `app/Http/Controllers/Admin/UserController.php`**

Replace the `edit` method body with:

```php
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
```

- [ ] **Step 4: Update `update()` to validate + sync links two-way**

In `app/Http/Controllers/Admin/UserController.php`, add the linked-accounts rule to the `update` validation array (after `'office_id'`):

```php
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
```

Then, at the end of `update()` (just before the `return redirect()...`), add the two-way sync:

```php
        $linkedIds = collect($validated['linked_accounts'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->all();

        $previous = $user->linkedAccounts()->pluck('users.id')->all();
        $user->linkedAccounts()->sync($linkedIds);

        // Mirror the change on the other side so links stay symmetric.
        foreach (array_diff($linkedIds, $previous) as $id) {
            User::find($id)?->linkedAccounts()->syncWithoutDetaching([$user->id]);
        }
        foreach (array_diff($previous, $linkedIds) as $id) {
            User::find($id)?->linkedAccounts()->detach($user->id);
        }
```

(`Rule` is already imported in this controller.)

- [ ] **Step 5: Add the "Akun Terkait" field to `resources/views/admin/users/edit.blade.php`**

Immediately before the `<!-- Submit -->` block, add (only shown when editing a non-admin user):

```blade
                @unless ($user->isAdmin())
                    <hr class="admin-divider">

                    <!-- Linked Accounts -->
                    <div>
                        <label class="admin-label">Akun Terkait (Ganti Akun Cepat)</label>
                        <p class="admin-hint mb-2">Akun non-admin yang boleh saling berpindah tanpa login ulang.</p>
                        <div class="max-h-56 space-y-1.5 overflow-y-auto rounded-xl border border-white/10 p-3">
                            @forelse ($linkableUsers as $candidate)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="linked_accounts[]" value="{{ $candidate->id }}"
                                        class="admin-checkbox rounded"
                                        {{ in_array($candidate->id, old('linked_accounts', $linkedIds)) ? 'checked' : '' }}>
                                    <span>{{ $candidate->name }}
                                        <span class="admin-muted">— {{ $candidate->office?->name ?? 'Tanpa kantor' }}</span>
                                    </span>
                                </label>
                            @empty
                                <p class="admin-muted text-sm">Belum ada akun non-admin lain.</p>
                            @endforelse
                        </div>
                        @error('linked_accounts.0')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                @endunless
```

- [ ] **Step 6: Run the tests to verify they pass**

Run: `php artisan test tests/Feature/Admin/AccountLinkTest.php`
Expected: PASS (all Task 1 + Task 2 tests).

- [ ] **Step 7: Format & commit**

```bash
./vendor/bin/pint app/Http/Controllers/Admin/UserController.php
git add app/Http/Controllers/Admin/UserController.php resources/views/admin/users/edit.blade.php tests/Feature/Admin/AccountLinkTest.php
git commit -m "feat: let admin link non-admin accounts on the user edit page"
```

---

### Task 3: `account_switch_logs` table + model

**Files:**
- Create: `database/migrations/2026_07_20_000002_create_account_switch_logs_table.php`
- Create: `app/Models/AccountSwitchLog.php`
- Test: `tests/Feature/Employee/AccountSwitchTest.php`

**Interfaces:**
- Produces: `AccountSwitchLog` with `$fillable = ['from_user_id', 'to_user_id', 'ip_address']`, relations `fromUser()` / `toUser()`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Employee/AccountSwitchTest.php

use App\Models\AccountSwitchLog;
use App\Models\Role;
use App\Models\User;

function switchGuru(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

test('account switch log stores from/to and exposes relations', function () {
    $a = switchGuru('A');
    $b = switchGuru('B');

    $log = AccountSwitchLog::create([
        'from_user_id' => $a->id,
        'to_user_id' => $b->id,
        'ip_address' => '127.0.0.1',
    ]);

    expect($log->fromUser->id)->toBe($a->id)
        ->and($log->toUser->id)->toBe($b->id)
        ->and($log->ip_address)->toBe('127.0.0.1');
});
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test tests/Feature/Employee/AccountSwitchTest.php`
Expected: FAIL — `Class "App\Models\AccountSwitchLog" not found`.

- [ ] **Step 3: Create the migration**

```php
<?php
// database/migrations/2026_07_20_000002_create_account_switch_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_switch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_switch_logs');
    }
};
```

- [ ] **Step 4: Create `app/Models/AccountSwitchLog.php`**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AccountSwitchLog - Audit trail of fast account switches.
 */
class AccountSwitchLog extends Model
{
    /**
     * Only a created_at timestamp is kept for this append-only log.
     */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'ip_address',
    ];

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test tests/Feature/Employee/AccountSwitchTest.php`
Expected: PASS.

- [ ] **Step 6: Format & commit**

```bash
./vendor/bin/pint app/Models/AccountSwitchLog.php tests/Feature/Employee/AccountSwitchTest.php
git add database/migrations/2026_07_20_000002_create_account_switch_logs_table.php app/Models/AccountSwitchLog.php tests/Feature/Employee/AccountSwitchTest.php
git commit -m "feat: add account_switch_logs table and model"
```

---

### Task 4: Switch flow (controller + route + dashboard UI)

**Files:**
- Create: `app/Http/Controllers/Employee/AccountSwitchController.php`
- Modify: `routes/web.php` (employee `auth` group)
- Modify: `app/Http/Controllers/Employee/DashboardController.php` (pass `linkedAccounts`)
- Modify: `resources/views/attendance/dashboard.blade.php` (header "Ganti Akun" control)
- Test: `tests/Feature/Employee/AccountSwitchTest.php` (append)

**Interfaces:**
- Consumes: `User::linkedAccounts()` (Task 1), `AccountSwitchLog` (Task 3).
- Produces: `POST /account/switch` named `account.switch` (field `target_id`); `DashboardController` passes `$linkedAccounts`.

- [ ] **Step 1: Write the failing tests (append to `tests/Feature/Employee/AccountSwitchTest.php`)**

```php
test('a user can switch to a linked account', function () {
    $mi = switchGuru('Guru MI');
    $smp = switchGuru('Guru SMP');
    $mi->linkedAccounts()->attach($smp->id);
    $smp->linkedAccounts()->attach($mi->id);

    $this->actingAs($mi)
        ->post(route('account.switch'), ['target_id' => $smp->id])
        ->assertRedirect(route('attendance.dashboard'))
        ->assertSessionHas('success');

    $this->assertAuthenticatedAs($smp);
    expect(AccountSwitchLog::where('from_user_id', $mi->id)->where('to_user_id', $smp->id)->count())->toBe(1);
});

test('the switch endpoint is rate limited', function () {
    $mi = switchGuru('Guru MI');
    $other = switchGuru('Orang Lain'); // not linked -> each hit is a 403, but still counts toward the limit

    $this->actingAs($mi);

    // throttle:10,1 -> the 11th request within the window is throttled (429),
    // regardless of the controller's own 403.
    for ($i = 0; $i < 10; $i++) {
        $this->post(route('account.switch'), ['target_id' => $other->id])->assertForbidden();
    }

    $this->post(route('account.switch'), ['target_id' => $other->id])->assertStatus(429);
});

test('switching to a non-linked account is forbidden', function () {
    $mi = switchGuru('Guru MI');
    $other = switchGuru('Orang Lain');

    $this->actingAs($mi)
        ->post(route('account.switch'), ['target_id' => $other->id])
        ->assertForbidden();

    $this->assertAuthenticatedAs($mi);
    expect(AccountSwitchLog::count())->toBe(0);
});

test('switching to an admin account is forbidden even if linked', function () {
    $mi = switchGuru('Guru MI');
    $adminRole = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    // Force a (disallowed) link row directly to prove the server guard blocks it.
    $mi->linkedAccounts()->attach($admin->id);

    $this->actingAs($mi)
        ->post(route('account.switch'), ['target_id' => $admin->id])
        ->assertForbidden();

    $this->assertAuthenticatedAs($mi);
});

test('the dashboard shows a switch control for linked accounts', function () {
    $mi = switchGuru('Guru MI');
    $smp = switchGuru('Guru SMP');
    $mi->linkedAccounts()->attach($smp->id);

    $this->actingAs($mi)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('Ganti Akun')
        ->assertSee('Guru SMP');
});
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test tests/Feature/Employee/AccountSwitchTest.php`
Expected: FAIL — `Route [account.switch] not defined`.

- [ ] **Step 3: Create `app/Http/Controllers/Employee/AccountSwitchController.php`**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AccountSwitchLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AccountSwitchController - Fast, password-less switching between
 * admin-linked non-admin accounts.
 */
class AccountSwitchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $current = $request->user();
        $target = User::findOrFail($validated['target_id']);

        // Server-authoritative guard (never trust the UI):
        // both parties must be non-admin, target must not be self, and the
        // target must be in the current account's linked set.
        abort_if($current->isAdmin() || $target->isAdmin(), 403);
        abort_if($target->id === $current->id, 403);
        abort_unless($current->linkedAccounts()->whereKey($target->id)->exists(), 403);

        AccountSwitchLog::create([
            'from_user_id' => $current->id,
            'to_user_id' => $target->id,
            'ip_address' => $request->ip(),
        ]);

        Auth::login($target);
        $request->session()->regenerate();

        return redirect()
            ->route('attendance.dashboard')
            ->with('success', "Berpindah ke akun {$target->name}.");
    }
}
```

- [ ] **Step 4: Register the route in `routes/web.php`**

Add `use App\Http\Controllers\Employee\AccountSwitchController;` near the other controller imports, then add this line inside the authenticated employee group (the same `Route::middleware([...'auth'...])` group that defines `attendance.dashboard`), next to the attendance routes:

```php
    Route::post('account/switch', [AccountSwitchController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('account.switch');
```

- [ ] **Step 5: Pass linked accounts to the dashboard**

In `app/Http/Controllers/Employee/DashboardController.php`, add `'linkedAccounts' => $user->linkedAccounts()->with('office')->orderBy('name')->get(),` to the array passed to `view('attendance.dashboard', [...])`.

- [ ] **Step 6: Add the switch control to `resources/views/attendance/dashboard.blade.php`**

Inside the header actions `<div class="flex items-center gap-2.5">` (the one holding the theme toggle and logout), add this **before** the theme-toggle button:

```blade
                        @if ($linkedAccounts->isNotEmpty())
                            <div x-data="{ open: false }" class="relative">
                                <button type="button" @click="open = !open"
                                    class="theme-toggle w-7.5 h-7.5 rounded-lg glass-card theme-border flex items-center justify-center theme-text-muted hover:theme-text-main hover:scale-105 active:scale-95 transition-all duration-300"
                                    aria-label="Ganti Akun">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak @click.away="open = false"
                                    class="absolute right-0 mt-2 w-56 rounded-xl glass-card theme-border p-2 z-30 text-left">
                                    <p class="px-2 py-1 text-[9px] uppercase tracking-wider theme-text-muted font-outfit">Ganti Akun</p>
                                    @foreach ($linkedAccounts as $account)
                                        <form method="POST" action="{{ route('account.switch') }}"
                                            @submit.prevent="if (confirm('Ganti ke akun {{ $account->name }}?')) $el.submit()">
                                            @csrf
                                            <input type="hidden" name="target_id" value="{{ $account->id }}">
                                            <button type="submit"
                                                class="w-full flex items-center gap-2 rounded-lg px-2 py-2 text-xs theme-text-main hover:bg-white/5 transition-colors">
                                                <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-cyan-400 to-emerald-400 flex items-center justify-center text-[9px] font-bold text-slate-950">
                                                    {{ $account->initials() }}
                                                </span>
                                                <span class="truncate text-left">
                                                    {{ $account->name }}
                                                    <span class="block text-[9px] theme-text-muted">{{ $account->office?->name ?? 'Tanpa kantor' }}</span>
                                                </span>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif
```

> Note: the `admin-confirm` modal is admin-only, so this employee-side control uses a small inline confirm via Alpine `@submit.prevent` + `confirm()`; the native prompt is acceptable here for the low-risk, same-person switch.

- [ ] **Step 7: Run the tests to verify they pass**

Run: `php artisan test tests/Feature/Employee/AccountSwitchTest.php`
Expected: PASS (all Task 3 + Task 4 tests).

- [ ] **Step 8: Format & commit**

```bash
./vendor/bin/pint app/Http/Controllers/Employee/AccountSwitchController.php app/Http/Controllers/Employee/DashboardController.php
git add app/Http/Controllers/Employee/AccountSwitchController.php routes/web.php app/Http/Controllers/Employee/DashboardController.php resources/views/attendance/dashboard.blade.php tests/Feature/Employee/AccountSwitchTest.php
git commit -m "feat: password-less switching between linked accounts"
```

---

### Task 5: Admin-viewable switch log page

**Files:**
- Create: `app/Http/Controllers/Admin/AccountSwitchLogController.php`
- Create: `resources/views/admin/account-switches/index.blade.php`
- Modify: `routes/web.php` (admin group)
- Modify: `resources/views/components/layouts/app/sidebar.blade.php`
- Test: `tests/Feature/Admin/AccountSwitchLogPageTest.php`

**Interfaces:**
- Consumes: `AccountSwitchLog` (Task 3).
- Produces: `GET admin/account-switches` named `admin.account-switches.index`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Admin/AccountSwitchLogPageTest.php

use App\Models\AccountSwitchLog;
use App\Models\Role;
use App\Models\User;

function logAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function logGuru(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

test('admin sees the account switch log entries', function () {
    $a = logGuru('Guru MI');
    $b = logGuru('Guru SMP');
    AccountSwitchLog::create(['from_user_id' => $a->id, 'to_user_id' => $b->id, 'ip_address' => '10.0.0.1']);

    $this->actingAs(logAdmin())
        ->get(route('admin.account-switches.index'))
        ->assertStatus(200)
        ->assertSee('Guru MI')
        ->assertSee('Guru SMP')
        ->assertSee('10.0.0.1');
});

test('non-admin cannot open the account switch log page', function () {
    $this->actingAs(logGuru('Guru MI'))
        ->get(route('admin.account-switches.index'))
        ->assertRedirect(route('attendance.dashboard'));
});
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test tests/Feature/Admin/AccountSwitchLogPageTest.php`
Expected: FAIL — `Route [admin.account-switches.index] not defined`.

- [ ] **Step 3: Create `app/Http/Controllers/Admin/AccountSwitchLogController.php`**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountSwitchLog;
use Illuminate\View\View;

/**
 * AccountSwitchLogController - Read-only audit view of account switches.
 */
class AccountSwitchLogController extends Controller
{
    public function index(): View
    {
        $logs = AccountSwitchLog::with(['fromUser', 'toUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.account-switches.index', ['logs' => $logs]);
    }
}
```

- [ ] **Step 4: Register the route in `routes/web.php`**

Add inside the `admin` group (near the reports routes):

```php
    Route::get('account-switches', [Admin\AccountSwitchLogController::class, 'index'])->name('account-switches.index');
```

- [ ] **Step 5: Create `resources/views/admin/account-switches/index.blade.php`**

```blade
<x-layouts.app :title="'Riwayat Ganti Akun'">
    <div class="space-y-6">
        <x-admin.page-header kicker="Keamanan" title="Riwayat Ganti Akun"
            description="Catatan setiap perpindahan akun cepat" :count="$logs->total() . ' entri'" />

        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Waktu</th>
                            <th class="px-6 py-4 text-left">Dari Akun</th>
                            <th class="px-6 py-4 text-left">Ke Akun</th>
                            <th class="px-6 py-4 text-left">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $log->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold">{{ $log->fromUser?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold">{{ $log->toUser?->name ?? '-' }}</td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 font-mono text-xs">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-admin.empty-state icon="fas-right-left" title="Belum ada riwayat"
                                        hint="Perpindahan akun cepat akan tercatat di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="admin-panel-footer">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
```

- [ ] **Step 6: Add the sidebar link in `resources/views/components/layouts/app/sidebar.blade.php`**

After the "Informasi" (`admin.announcements.*`) sidebar link, add:

```blade
                                <x-layouts.sidebar-link href="{{ route('admin.account-switches.index') }}"
                                    icon='fas-right-left' :active="request()->routeIs('admin.account-switches.*')">Riwayat Ganti Akun</x-layouts.sidebar-link>
```

- [ ] **Step 7: Run the test to verify it passes**

Run: `php artisan test tests/Feature/Admin/AccountSwitchLogPageTest.php`
Expected: PASS.

- [ ] **Step 8: Full suite + format + commit**

```bash
php artisan test
./vendor/bin/pint app/Http/Controllers/Admin/AccountSwitchLogController.php
git add app/Http/Controllers/Admin/AccountSwitchLogController.php resources/views/admin/account-switches/index.blade.php routes/web.php resources/views/components/layouts/app/sidebar.blade.php tests/Feature/Admin/AccountSwitchLogPageTest.php
git commit -m "feat: admin-viewable account switch audit log"
```

---

## Notes for the implementer

- The employee `auth` route group is the one in `routes/web.php` that defines `attendance.dashboard` (around line 30). Put `account.switch` there so it inherits `auth` — do NOT put it in the `admin` group.
- After running `php artisan test`, if PHPStan is part of the gate, run `./vendor/bin/phpstan analyse <changed files>` and confirm no new errors vs the committed baseline (diff the sorted error list against `git show HEAD:<file>`), as done in prior work.
- `whereKeyNot` / `whereKey` are Eloquent builder helpers (primary-key filters).
- Do not run `pint`/`phpstan` repo-wide; only on the files you changed.
