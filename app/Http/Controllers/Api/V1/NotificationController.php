<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends ApiController
{
    /**
     * The authenticated user's notifications only - never another user's.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage($request));

        $payload = $this->paginated($notifications, NotificationResource::class, 'Data notifikasi berhasil diambil.');
        $data = $payload->getData(true);
        $data['meta']['unread_count'] = $request->user()->unreadNotifications()->count();

        return response()->json($data);
    }

    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage($request));

        return $this->paginated($notifications, NotificationResource::class, 'Notifikasi belum dibaca berhasil diambil.');
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): JsonResponse
    {
        // Ownership guard: a user may only mark their own notifications.
        abort_unless(
            $notification->notifiable_id === $request->user()->id
                && $notification->notifiable_type === get_class($request->user()),
            403,
            'Unauthorized',
        );

        $notification->update(['read_at' => now()]);

        return $this->success(message: 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return $this->success(message: 'Semua notifikasi ditandai sudah dibaca.');
    }
}
