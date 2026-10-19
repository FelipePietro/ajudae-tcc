<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    protected $table = 'evento';

    protected $primaryKey = 'evento_id';

    protected $hidden = ['managed_image_disk', 'managed_image_path', 'managed_image_mime', 'managed_image_size'];

    public $timestamps = true;

    protected $fillable = [
        'logradouro_evento',
        'cep_evento',
        'cidade_evento',
        'bairro_evento',
        'uf_evento',

        'compl_evento',

        'nm_evento',
        'descricao_evento',
        'vagas_evento',
        'modalidade_evento',
        'status_evento',
        'imagem_evento_link',

        'cat_evento_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cancelado_em' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Evento $evento) {
            $hasOng = $evento->ong_id !== null;
            $hasPessoa = $evento->pessoa_id !== null;

            if ($hasOng === $hasPessoa) {
                throw new \DomainException('Um evento deve possuir exatamente um responsável.');
            }
        });
    }

    public function ong(): BelongsTo
    {
        return $this->belongsTo(
            Ong::class,
            'ong_id',
            'ong_id'
        );
    }

    public function pessoa()
    {
        return $this->belongsTo(
            Pessoa::class,
            'pessoa_id',
            'pessoa_id'
        );
    }

    public function responsavelPessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'responsavel_pessoa_id', 'pessoa_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            CatEvento::class,
            'cat_evento_id',
            'cat_evento_id'
        );
    }

    public function habilidades()
    {
        return $this->belongsToMany(
            Habilidade::class,
            'evento_habilidade',
            'evento_id',
            'habilidade_id'
        );
    }

    public function causas(): BelongsToMany
    {
        return $this->belongsToMany(Causa::class, 'evento_causa', 'evento_id', 'causa_id');
    }

    public function recursos(): BelongsToMany
    {
        return $this->belongsToMany(Recurso::class, 'evento_recurso', 'evento_id', 'recurso_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class, 'evento_id', 'evento_id');
    }
}
