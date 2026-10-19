<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoPessoa extends Model
{
    public const TIPOS = ['documento_identidade', 'antecedentes_criminais', 'cnh'];

    protected $table = 'documento_pessoa';

    protected $primaryKey = 'documento_pessoa_id';

    protected $fillable = [
        'pessoa_id',
        'tipo',
        'storage_path',
        'storage_disk',
        'mime_type',
        'size_bytes',
        'sha256',
        'status',
        'ativo',
        'uploaded_at',
        'validated_at',
        'validated_by_admin_id',
        'rejection_reason',
        'expires_at',
        'alerta_renovacao_em',
        'delete_after',
        'arquivo_excluido_em',
    ];

    protected $hidden = ['storage_path', 'storage_disk', 'sha256'];

    protected $casts = [
        'ativo' => 'boolean',
        'uploaded_at' => 'datetime',
        'validated_at' => 'datetime',
        'expires_at' => 'datetime',
        'alerta_renovacao_em' => 'datetime',
        'delete_after' => 'datetime',
        'arquivo_excluido_em' => 'datetime',
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id', 'pessoa_id');
    }

    public function adminValidador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'validated_by_admin_id', 'pessoa_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }
}
