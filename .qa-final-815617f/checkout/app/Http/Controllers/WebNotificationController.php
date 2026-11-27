<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class WebNotificationController extends Controller
{
    private const CATEGORIES = [
        'agenda' => 'Agenda',
        'aviso' => 'Avisos gerais',
        'evento' => 'Evento',
        'inscricao' => 'Candidatura',
        'lgpd' => 'Privacidade',
        'organizador' => 'Organização',
        'padrinho' => 'Apadrinhamento',
        'validacao' => 'Validação cadastral',
    ];

    public function index(Request $request): JsonResponse
    {
        $account = $this->account($request);
        $filters = $request->validate([
            'busca' => ['nullable', 'string', 'max:100'],
            'leitura' => ['nullable', Rule::in(['todas', 'nao-lidas', 'lidas'])],
            'tipo' => ['nullable', Rule::in(array_keys(self::CATEGORIES))],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $inbox = $account->notificacoes();
        $total = (clone $inbox)->count();
        $unread = (clone $inbox)->where('status_confirmacao', 'não lido')->count();
        $typeCodes = (clone $inbox)->distinct()->pluck('tipo_notificacao');
        $types = $typeCodes->map(fn (?string $type) => $this->category($type))->unique()
            ->mapWithKeys(fn (string $category) => [$category => self::CATEGORIES[$category]])
            ->sort()->all();

        $query = $inbox->getQuery();
        $read = match ($filters['leitura'] ?? null) {
            'lidas' => 'lido',
            'nao-lidas' => 'não lido',
            default => null,
        };
        if ($read !== null) {
            $query->where('status_confirmacao', $read);
        }
        if (isset($filters['tipo'])) {
            $selectedTypes = $typeCodes->filter(fn (?string $type) => $this->category($type) === $filters['tipo']);
            $query->where(function ($query) use ($selectedTypes): void {
                $query->whereIn('tipo_notificacao', $selectedTypes->filter(fn (?string $type) => $type !== null));
                if ($selectedTypes->containsStrict(null)) {
                    $query->orWhereNull('tipo_notificacao');
                }
            });
        }
        $term = $filters['busca'] ?? '';
        if ($term !== '') {
            $pattern = '%'.strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
            $matchedTypes = $typeCodes->filter(fn (?string $type) => $type !== null
                && str_contains(mb_strtolower($this->title($type)), mb_strtolower($term)));
            $query->where(fn ($query) => $query->whereRaw("LOWER(mensagem_notificacao) LIKE LOWER(?) ESCAPE '!'", [$pattern])
                ->orWhereIn('tipo_notificacao', $matchedTypes));
        }

        $query->latest()->orderByDesc('notificacao_id');
        $page = (clone $query)->paginate(8, ['*'], 'page', $filters['page'] ?? 1);
        if ($page->currentPage() > $page->lastPage()) {
            $page = $query->paginate(8, ['*'], 'page', $page->lastPage());
        }

        return ApiResponse::success([
            'items' => $page->getCollection()->map(fn (Notification $notification) => [
                'id' => $notification->getKey(),
                'title' => $this->title($notification->tipo_notificacao),
                'category' => self::CATEGORIES[$this->category($notification->tipo_notificacao)],
                'message' => $notification->mensagem_notificacao,
                'read' => $notification->status_confirmacao === 'lido',
                'created_at' => $notification->created_at?->toISOString(),
                'read_url' => route('identity.notifications.read', ['id' => $notification->getKey()], false),
                'details_url' => $this->detailsUrl($account, $notification->tipo_notificacao),
            ]),
            'total' => $total,
            'unread' => $unread,
            'page' => $page->currentPage(),
            'pages' => $page->lastPage(),
            'types' => (object) $types,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        abort_if(filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false, 404);

        $this->updateInbox($request, function (Pessoa|Ong $account) use ($id): void {
            $notification = $account->notificacoes()->lockForUpdate()->findOrFail($id);
            if ($notification->status_confirmacao !== 'lido') {
                $notification->update(['status_confirmacao' => 'lido']);
            }
        });

        return ApiResponse::success(null, 'Notificação marcada como lida.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->updateInbox($request, fn (Pessoa|Ong $account) => $account->notificacoes()
            ->where('status_confirmacao', 'não lido')->update(['status_confirmacao' => 'lido']));

        return ApiResponse::success(null, 'Notificações marcadas como lidas.');
    }

    private function account(Request $request): Pessoa|Ong
    {
        $account = $request->attributes->get('web_account');
        abort_unless($account instanceof Pessoa || $account instanceof Ong, 401);
        abort_unless(AccountAccess::canOperate($account), 403);

        return $account;
    }

    private function updateInbox(Request $request, callable $update): void
    {
        $identity = $this->account($request);

        DB::transaction(function () use ($identity, $update): void {
            $account = $identity->newQuery()->lockForUpdate()->findOrFail($identity->getKey());
            abort_unless((int) $account->auth_version === (int) $identity->auth_version && AccountAccess::canOperate($account), 403);
            $update($account);
        }, 3);
    }

    private function category(?string $type): string
    {
        $prefix = explode('.', $type ?? '', 2)[0];

        return match ($prefix) {
            'agenda', 'evento', 'inscricao', 'lgpd', 'padrinho' => $prefix,
            'antecedentes', 'validacao' => 'validacao',
            'organizador', 'vinculo', 'convite' => 'organizador',
            default => 'aviso',
        };
    }

    private function title(?string $type): string
    {
        return match ($type) {
            'agenda.alterada' => 'Agenda alterada',
            'agenda.cancelada' => 'Agenda cancelada',
            'antecedentes.renovacao_necessaria' => 'Renovação de antecedentes',
            'evento.alterado' => 'Evento alterado',
            'evento.cancelado' => 'Evento cancelado',
            'evento.status_atualizado' => 'Situação do evento atualizada',
            'inscricao.criada' => 'Nova candidatura',
            'inscricao.desistente' => 'Desistência registrada',
            'inscricao.status_atualizado' => 'Situação da candidatura atualizada',
            'lgpd.status_atualizado' => 'Solicitação de privacidade atualizada',
            default => self::CATEGORIES[$this->category($type)],
        };
    }

    private function detailsUrl(Pessoa|Ong $account, ?string $type): ?string
    {
        return match (explode('.', $type ?? '', 2)[0]) {
            'antecedentes', 'validacao', 'padrinho' => AccountAccess::hasPersonalJourney($account)
                ? route('perfil', absolute: false) : null,
            'lgpd' => $account instanceof Ong
                ? route('ong.privacidade', absolute: false)
                : (AccountAccess::hasPersonalJourney($account) ? route('perfil.privacidade', absolute: false) : null),
            default => null,
        };
    }
}
