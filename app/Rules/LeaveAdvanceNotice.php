<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LeaveAdvanceNotice implements ValidationRule
{
    public const CUTOFF_HOUR = 21;

    public function __construct(private readonly ?string $leaveType) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array($this->leaveType, ['izin', 'cuti'], true) || ! is_string($value)) {
            return;
        }

        try {
            $startDate = CarbonImmutable::createFromFormat('!Y-m-d', $value, config('app.timezone'));
        } catch (\Throwable) {
            return;
        }

        $cutoff = $startDate === false
            ? null
            : $startDate->subDay()->setTime(self::CUTOFF_HOUR, 0);

        if ($cutoff === null || now()->greaterThan($cutoff)) {
            $fail('Izin atau cuti harus diajukan paling lambat pukul 21.00 pada hari sebelumnya.');
        }
    }
}
