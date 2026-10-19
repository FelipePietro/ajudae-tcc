<?php

namespace App\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notificacoes()
            ->orderByDesc('created_at')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success($notifications);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success($request->user()->notificacoes()->findOrFail($id));
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = $request->user()->notificacoes()->findOrFail($id);

        $notification->update([
            'status_confirmacao' => 'lido',
        ]);

        return ApiResponse::success($notification->fresh(), 'Notificação marcada como lida.');
    }

    public function destroy(Request $request, int $id)
    {
        $request->user()->notificacoes()->findOrFail($id)->delete();

        return response()->noContent();
    }
}
