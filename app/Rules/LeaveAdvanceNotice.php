<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeaveAdvanceNotice implements ValidationRule
{
    public const HOURS = 12;

    public function __construct(private readonly ?string $leaveType) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array($this->leaveType, ['izin', 'cuti'], true) || ! is_string($value)) {
            return;
        }

        try {
            $startsAt = CarbonImmutable::createFromFormat('!Y-m-d', $value, config('app.timezone'));
        } catch (\Throwable) {
            return;
        }

        if ($startsAt === false || now()->greaterThan($startsAt->subHours(self::HOURS))) {
            $fail('Izin atau cuti harus diajukan minimal 12 jam sebelum tanggal mulai.');
        }
    }
}
