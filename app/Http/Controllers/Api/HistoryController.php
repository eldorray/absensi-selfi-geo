<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HistoryRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class HistoryController extends Controller
{
    /**
     * Rows per page. Server-side so a long-serving teacher's history never
     * arrives in one response.
     */
    private const PER_PAGE = 15;

    /**
     * The signed-in teacher's attendance history, newest first.
     *
     * The query is keyed on the token's own user id, so there is no id in the
     * request that could point at somebody else's records.
     */
    public function index(HistoryRequest $request): JsonResponse
    {
        $query = Attendance::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        $month = $request->validated('month');

        if (is_string($month)) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

            $query->whereBetween('created_at', [$start, $start->copy()->endOfMonth()]);
        }

        $page = $query->paginate(self::PER_PAGE);

        return response()->json([
            'data' => AttendanceResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}
