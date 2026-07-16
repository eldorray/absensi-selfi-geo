<?php

use App\Models\WorkSetting;
use App\Support\FineCalculator;

function fineSettings(): WorkSetting
{
    return new WorkSetting([
        'after_check_in' => 10,
        'fine_tier1_amount' => 5000,
        'fine_tier2_amount' => 10000,
        'fine_tier1_max_minutes' => 15,
    ]);
}

test('no fine when not late', function () {
    expect(FineCalculator::amountForMinutes(0, fineSettings()))->toBe(0);
});

test('tier 1 applies from 1 up to and including the boundary', function () {
    $s = fineSettings();
    expect(FineCalculator::amountForMinutes(1, $s))->toBe(5000);
    expect(FineCalculator::amountForMinutes(15, $s))->toBe(5000);
});

test('tier 2 applies above the boundary', function () {
    $s = fineSettings();
    expect(FineCalculator::amountForMinutes(16, $s))->toBe(10000);
    expect(FineCalculator::amountForMinutes(120, $s))->toBe(10000);
});

test('respects dynamic amounts and boundary from settings', function () {
    $s = new WorkSetting([
        'fine_tier1_amount' => 2000,
        'fine_tier2_amount' => 7500,
        'fine_tier1_max_minutes' => 30,
    ]);
    expect(FineCalculator::amountForMinutes(30, $s))->toBe(2000);
    expect(FineCalculator::amountForMinutes(31, $s))->toBe(7500);
});
