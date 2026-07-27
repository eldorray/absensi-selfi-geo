<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\EmployeeDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly EmployeeDashboardService $dashboard,
    ) {}

    /**
     * The signed-in teacher's beranda. Scoped to the token's own user, so one
     * teacher can never read another's figures.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('office');

        return response()->json(
            new DashboardResource($this->dashboard->for($user), $user)
        );
    }
}
