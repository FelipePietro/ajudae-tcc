<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->orderByDesc('sq_notificacao')
            ->get();

        return response()->json($notifications);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::query()->findOrFail($id);

        $notification->update([
            'status_confirmacao' => 'lido',
        ]);

        return response()->json([
            'message' => 'Notificacao marcada como lida.',
            'data' => $notification->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::query()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notificacao removida com sucesso.',
        ]);
    }
}
