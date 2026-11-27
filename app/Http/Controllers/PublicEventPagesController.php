<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Services\ManagedImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class PublicEventPagesController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->query();
        $search = is_string($filters['q'] ?? null) ? mb_substr(trim($filters['q']), 0, 120) : '';
        $city = is_string($filters['cidade'] ?? null) ? mb_substr(trim($filters['cidade']), 0, 120) : '';
        $mode = $filters['modalidade'] ?? '';
        $mode = in_array($mode, ['presencial', 'online', 'hibrido'], true) ? $mode : '';
        $selected = ['q' => $search, 'cidade' => $city, 'modalidade' => $mode];
        $catalog = $this->published();
        $cities = (clone $catalog)->select('cidade_evento')->whereNotNull('cidade_evento')->where('cidade_evento', '<>', '')
            ->distinct()->orderBy('cidade_evento')->pluck('cidade_evento');

        if ($search !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
            $catalog->where(fn (Builder $query) => $query
                ->whereRaw("nm_evento LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("cidade_evento LIKE ? ESCAPE '!'", [$pattern]));
        }

        $catalog->when($city !== '', fn (Builder $query) => $query->where('cidade_evento', $city))
            ->when($mode !== '', fn (Builder $query) => $query->where('modalidade_evento', $mode));

        return view('feed.index', [
            'publicEvents' => $catalog->orderByDesc('evento_id')->paginate(12)
                ->appends(array_filter($selected, fn (string $value) => $value !== '')),
            'publishedAvailable' => $this->published()->exists(),
            'publicFilters' => $selected,
            'cidades' => $cities,
        ]);
    }

    public function show(Request $request, ManagedImageStorage $images, string $id)
    {
        $evento = $this->published()->addSelect([
            'imagem_evento_link', 'managed_image_disk', 'managed_image_path',
            'managed_image_mime', 'managed_image_size',
        ])->whereKey($id)->firstOrFail();

        return view('eventos.detalhe', [
            'publicEvent' => $evento,
            'publicEventCover' => $images->safeReference($evento)
                ? $images->publicUrl($evento)
                : $this->capaLegada($evento->imagem_evento_link, $request->getHost()),
        ]);
    }

    private function capaLegada(?string $link, string $host): ?string
    {
        if ($link === null || $link === '' || str_contains($link, '\\') || str_starts_with($link, '//')
            || preg_match('/[\x00-\x20\x7f]/', $link)) {
            return null;
        }
        $partes = parse_url($link);
        if ($partes === false) {
            return null;
        }
        if (isset($partes['scheme'])) {
            if (! in_array(strtolower($partes['scheme']), ['https', 'http'], true)
                || ! filter_var($link, FILTER_VALIDATE_URL) || isset($partes['user']) || isset($partes['pass'])) {
                return null;
            }
            if (strcasecmp($partes['host'], $host) !== 0) {
                return $link;
            }
        }

        // Uma referência local só pode apontar a um recurso público de imagem.
        $caminho = rawurldecode($partes['path'] ?? '');
        if (str_contains($caminho, '\\')
            || preg_match('~(?:^|/)(?:\.{1,2}|gestao|conta|admin|api|documentos|private|railway_private)(?:/|$)~i', $caminho)
            || ! preg_match('/\.(?:avif|gif|jpe?g|png|svg|webp)\z/i', $caminho)) {
            return null;
        }

        return isset($partes['host']) ? $link : '/'.ltrim($link, '/');
    }

    public function application(Request $request)
    {
        $account = $request->attributes->get('web_account');
        abort_unless($account->isApprovedVolunteer(), 403);

        $id = $request->query('evento');
        abort_unless(is_string($id) && ctype_digit($id), 404);

        return view('candidatura', [
            'publicEvent' => $this->published()->whereKey($id)->firstOrFail(),
            'candidate' => $account->nm_pessoa,
        ]);
    }

    private function published(): Builder
    {
        return Evento::query()
            ->select('evento_id', 'nm_evento', 'descricao_evento', 'cidade_evento', 'uf_evento', 'modalidade_evento')
            ->publicado();
    }
}
