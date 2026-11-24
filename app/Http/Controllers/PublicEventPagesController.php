<?php

namespace App\Http\Controllers;

use App\Models\Evento;
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

    public function show(string $id)
    {
        return view('eventos.detalhe', [
            'publicEvent' => $this->published()->whereKey($id)->firstOrFail(),
        ]);
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
            ->where('status_evento', 'ativo');
    }
}
