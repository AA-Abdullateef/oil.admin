<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByRaw('read_at is not null')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $notifications->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type'] ?? null,
                'title'      => $n->data['title'] ?? null,
                'body'       => $n->data['body'] ?? null,
                'category'   => $n->data['category'] ?? 'general',
                'priority'   => $n->data['priority'] ?? 'normal',
                'severity'   => $n->data['severity'] ?? 'info',
                'action'     => $n->data['action'] ?? null,
                'data'       => collect($n->data)->except(['type', 'title', 'body', 'category', 'priority', 'severity', 'action'])->toArray(),
                'read'       => ! is_null($n->read_at),
                'read_at'    => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'meta' => [
                'unread_count' => $request->user()->unreadNotifications()->count(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);
        // if notification alreasy marked read
        if ($notification->read_at) {
            return response()->json(['message' => 'Notification already marked as read.']);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->user()->unreadNotifications()->count() === 0) {
            return response()->json(['message' => 'All notifications are already marked as read.']);
        }

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
