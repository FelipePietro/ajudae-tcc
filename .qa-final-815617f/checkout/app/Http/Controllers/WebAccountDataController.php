<?php

namespace App\Http\Controllers;

use App\Models\AdminLog;
use App\Models\Pessoa;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class WebAccountDataController extends Controller
{
    public function preferences(Request $request)
    {
        $pessoa = $request->user();
        abort_unless($pessoa instanceof Pessoa, 403);

        return ApiResponse::success([
            'habilidades' => $pessoa->habilidades()->get(),
            'recursos' => $pessoa->recursos()->get(),
            'causas' => $pessoa->causas()->get(),
        ]);
    }

    public function audit(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'acao' => ['sometimes', 'string', 'max:120'],
        ]);

        return ApiResponse::success(AdminLog::query()
            ->when(isset($validated['acao']), fn ($query) => $query->where('acao', $validated['acao']))
            ->orderByDesc('admin_log_id')
            ->paginate($validated['per_page'] ?? 20)
            ->through(fn (AdminLog $log) => $log->only([
                'admin_log_id', 'pessoa_id', 'acao', 'recurso_type', 'recurso_id', 'created_at',
            ])));
    }
}
