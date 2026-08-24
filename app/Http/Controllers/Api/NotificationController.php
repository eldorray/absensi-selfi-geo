<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Notifikasi internal Kesiswaan untuk klien native.
 *
 * Selalu difilter ke tipe StudentReferral*: kotak masuk ini bukan tempat
 * notifikasi umum, dan penyaringan di query mencegah notifikasi domain lain
 * ikut terbaca ketika kelak ditambahkan.
 *
 * Klien native tidak membuka URL web, jadi setiap baris membawa `referral_id`
 * agar bisa langsung menavigasi ke layar rujukan. Notifikasi lama yang tersimpan
 * sebelum field itu ada tetap dikembalikan dengan `referral_id` null — klien
 * memperlakukannya sebagai tidak dapat diketuk, bukan crash.
 */
class NotificationController extends Controller
{
    private const PER_PAGE = 20;

    private const TYPE_FILTER = '%StudentReferral%';

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->notifications()->where('type', 'like', self::TYPE_FILTER);

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $page = $query->latest()->paginate($this->perPage($request));

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()
                ->where('type', 'like', self::TYPE_FILTER)->count(),
            'data' => collect($page->items())
                ->map(fn (DatabaseNotification $notification): array => $this->row($notification))
                ->all(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * Menandai satu notifikasi terbaca dan mengembalikan tujuan navigasinya.
     */
    public function read(Request $request, string $notification): JsonResponse
    {
        $item = $request->user()->notifications()
            ->whereKey($notification)
            ->where('type', 'like', self::TYPE_FILTER)
            ->firstOrFail();

        $item->markAsRead();

        return response()->json(['data' => $this->row($item->refresh())]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()
            ->where('type', 'like', self::TYPE_FILTER)
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Semua notifikasi Kesiswaan telah dibaca.',
            'unread_count' => 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(DatabaseNotification $notification): array
    {
        $data = $notification->data;
        $status = $data['status'] ?? null;

        return [
            'id' => $notification->id,
            'referral_id' => $data['referral_id'] ?? null,
            'student_name' => $data['student_name'] ?? null,
            'status' => $status,
            'status_label' => match ($status) {
                'new' => 'Baru',
                'in_handling' => 'Ditangani',
                'completed' => 'Selesai',
                'rejected' => 'Ditolak',
                default => null,
            },
            'title' => $status === 'new' ? 'Rujukan baru' : 'Status rujukan diperbarui',
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', self::PER_PAGE)));
    }
}
