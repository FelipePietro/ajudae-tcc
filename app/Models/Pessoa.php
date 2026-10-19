<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pessoa extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nome da tabela no banco.
     */
    protected $table = 'pessoa';

    /**
     * Chave primária da tabela.
     */
    protected $primaryKey = 'pessoa_id';

    public $timestamps = true;

    /**
     * Tipo da chave primária.
     */
    protected $keyType = 'int';

    /**
     * A chave primária é auto incremento.
     */
    public $incrementing = true;

    /**
     * Campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'nm_pessoa',
        'genero_pessoa',
        'cpf_pessoa',
        'dt_nasc',
        'email_pessoa',
        'tele_pessoa',
        'cep_pessoa',
        'logradouro_pessoa',
        'compl_pessoa',
        'cidade_pessoa',
        'bairro_pessoa',
        'uf_pessoa',
        'rg_pessoa',
        'antecedentes_pessoa_link',
        'cnh_pessoa_link',
        'login_pessoa',
        'senha_pessoa',
        'pfp_pessoa_link',
        'rg_pessoa_link',
    ];

    /**
     * Campos ocultos em respostas JSON.
     */
    protected $hidden = [
        'senha_pessoa',
        'remember_token',
        'cpf_pessoa',
        'rg_pessoa',
        'antecedentes_pessoa_link',
        'cnh_pessoa_link',
        'rg_pessoa_link',
        'exclusao_pendente',
        'deletar_em',
        'admin_master',
        'managed_image_disk',
        'managed_image_path',
        'managed_image_mime',
        'managed_image_size',
    ];

    /**
     * Conversão automática de tipos.
     */
    protected $casts = [
        'dt_nasc' => 'date',
        'exclusao_pendente' => 'boolean',
        'deletar_em' => 'datetime',
        'admin_master' => 'boolean',
        'analise_assumida_em' => 'datetime',
        'aprovado_em' => 'datetime',
        'reprovado_definitivamente_em' => 'datetime',
        'bloqueado_ate' => 'datetime',
    ];

    /**
     * Informa ao Laravel qual coluna contém a senha.
     */
    public function getAuthPassword()
    {
        return $this->senha_pessoa;
    }

    public function notificacoes(): MorphMany
    {
        return $this->morphMany(Notification::class, 'destinatario');
    }

    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Habilidade::class,
            'habilidade_pessoa',
            'pessoa_id',
            'habilidade_id'
        )->withPivot('nivel_habilidade');
    }

    public function causas(): BelongsToMany
    {
        return $this->belongsToMany(
            Causa::class,
            'causa_pessoa',
            'pessoa_id',
            'causa_id'
        );
    }

    public function recursos(): BelongsToMany
    {
        return $this->belongsToMany(
            Recurso::class,
            'pessoa_recurso',
            'pessoa_id',
            'recurso_id'
        )->withPivot('detalhes_recurso');
    }

    public function inscricoes(): HasMany
    {
        return $this->hasMany(
            Inscricao::class,
            'pessoa_id',
            'pessoa_id'
        );
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'pessoa_id', 'pessoa_id');
    }

    public function eventosSobResponsabilidade(): HasMany
    {
        return $this->hasMany(Evento::class, 'responsavel_pessoa_id', 'pessoa_id');
    }

    public function solicitacoesOrganizador(): HasMany
    {
        return $this->hasMany(SolicitacaoOrganizador::class, 'pessoa_id', 'pessoa_id');
    }

    public function vinculosOng(): HasMany
    {
        return $this->hasMany(VinculoOngOrganizador::class, 'pessoa_id', 'pessoa_id');
    }

    public function vinculoOngAtivo(): HasOne
    {
        return $this->hasOne(VinculoOngOrganizador::class, 'pessoa_id', 'pessoa_id')
            ->where('status', 'ativo');
    }

    public function assinaturas()
    {
        return $this->morphMany(Assinatura::class, 'assinante');
    }

    public function permissoes(): BelongsToMany
    {
        return $this->belongsToMany(
            Permissao::class,
            'permissao_pessoa',
            'pessoa_id',
            'permissao_id'
        );
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role_pessoa !== 'administrador') {
            return false;
        }

        if ($this->admin_master) {
            return true;
        }

        return $this->permissoes()->where('nome', $permission)->exists();
    }

    public function isApprovedVolunteer(): bool
    {
        return $this->role_pessoa === 'voluntario'
            && $this->status_validacao === 'aprovado';
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoPessoa::class, 'pessoa_id', 'pessoa_id');
    }

    public function historicoValidacao(): HasMany
    {
        return $this->hasMany(HistoricoValidacaoPessoa::class, 'pessoa_id', 'pessoa_id');
    }
}
