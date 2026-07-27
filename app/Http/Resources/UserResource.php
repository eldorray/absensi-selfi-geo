<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The public shape of a user over the mobile API.
 *
 * @property-read User $resource
 */
class UserResource extends JsonResource
{
    /**
     * Only non-sensitive fields: no password, no visible_password, no token,
     * and no foreign keys that would expose other tenants' records.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'office' => $user->office?->name,
            'initials' => $user->initials(),
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
        ];
    }
}
