<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Models\Leave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeaveController extends Controller
{
    /**
     * Rows per page, matching the web list.
     */
    private const PER_PAGE = 10;

    /**
     * The signed-in teacher's own leave requests, newest first.
     *
     * Keyed on the token's user id — no id in the request can point at
     * somebody else's records.
     */
    public function index(Request $request): JsonResponse
    {
        $page = Leave::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE);

        return response()->json([
            'data' => LeaveResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * Submit a new leave request. Always starts as pending; the approver
     * flow stays on the web side.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = sprintf(
                '%d_%s_%s.%s',
                $request->user()->id,
                now()->format('Y-m-d'),
                Str::random(8),
                $file->getClientOriginalExtension()
            );
            $attachmentPath = $file->storeAs('leaves', $filename, 'public');
        }

        $leave = Leave::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return response()->json(new LeaveResource($leave), 201);
    }

    /**
     * One leave request — only ever the token owner's own row.
     */
    public function show(Request $request, Leave $leave): JsonResponse
    {
        abort_unless($leave->user_id === $request->user()->id, 404);

        return response()->json(new LeaveResource($leave));
    }
}
