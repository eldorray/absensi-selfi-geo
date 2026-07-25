<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * @property-read ?string $nip
 * @property-read ?string $nik
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'visible_password',
        'office_id',
        'role_id',
        'avatar_path',
        'nip',
        'nik',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'visible_password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'visible_password' => 'encrypted',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * URL for the uploaded avatar, or null when none set.
     *
     * Served through a route (avatar.show) rather than the public/storage
     * symlink, which some hosts (e.g. Hostinger LiteSpeed) do not serve.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        // Version by the stored path so a new upload busts the browser cache
        // (the route URL is otherwise identical across uploads).
        return route('avatar.show', ['user' => $this, 'v' => substr(md5($this->avatar_path), 0, 8)]);
    }

    /**
     * Get the role that the user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role?->is_admin ?? false;
    }

    /**
     * Check if the user is an employee (non-admin).
     */
    public function isEmployee(): bool
    {
        return ! $this->isAdmin();
    }

    /**
     * Get the office that the user belongs to.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get all attendance records for this user.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all work schedules for this user.
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    /**
     * Accounts this user may switch to (admin-linked, symmetric).
     */
    public function linkedAccounts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'account_links', 'user_id', 'linked_user_id');
    }
}
