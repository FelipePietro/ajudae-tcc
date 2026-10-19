<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Acompanhamento extends Model
{
    protected $table = 'acompanhamento';

    protected $primaryKey = 'acompanhamento_id';

    protected $fillable = [
        'padrinho_id', 'apadrinhado_id', 'status', 'inicio', 'fim', 'observacao',
        'aceito_em', 'encerrado_em', 'encerrado_por_id', 'motivo_encerramento',
        'concluido_em', 'concluido_por_id',
    ];

    protected $casts = [
        'inicio' => 'date', 'fim' => 'date', 'aceito_em' => 'datetime',
        'encerrado_em' => 'datetime', 'concluido_em' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Acompanhamento $acompanhamento) {
            if ($acompanhamento->status !== 'ativo') {
                return;
            }

            $jaExiste = static::query()
                ->where('padrinho_id', $acompanhamento->padrinho_id)
                ->where('apadrinhado_id', $acompanhamento->apadrinhado_id)
                ->where('status', 'ativo')
                ->when(
                    $acompanhamento->exists,
                    fn ($query) => $query->whereKeyNot($acompanhamento->getKey())
                )
                ->exists();

            if ($jaExiste) {
                throw new \DomainException('Já existe um acompanhamento ativo para esta dupla.');
            }
        });
    }

    public static function iniciar(
        int $padrinhoId,
        int $apadrinhadoId,
        array $attributes = []
    ): self {
        return DB::transaction(function () use ($padrinhoId, $apadrinhadoId, $attributes) {
            Pessoa::query()
                ->whereKey([$padrinhoId, $apadrinhadoId])
                ->orderBy('pessoa_id')
                ->lockForUpdate()
                ->get();

            if (static::query()
                ->where('padrinho_id', $padrinhoId)
                ->where('apadrinhado_id', $apadrinhadoId)
                ->where('status', 'ativo')
                ->exists()) {
                throw new \DomainException('Já existe um acompanhamento ativo para esta dupla.');
            }

            return static::create([
                ...$attributes,
                'padrinho_id' => $padrinhoId,
                'apadrinhado_id' => $apadrinhadoId,
                'status' => 'ativo',
                'inicio' => $attributes['inicio'] ?? now()->toDateString(),
            ]);
        });
    }

    public function padrinho(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'padrinho_id', 'pessoa_id');
    }

    public function apadrinhado(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'apadrinhado_id', 'pessoa_id');
    }

    public function conclusoes()
    {
        return $this->hasMany(ConclusaoPadrinho::class, 'acompanhamento_id', 'acompanhamento_id');
    }
}
