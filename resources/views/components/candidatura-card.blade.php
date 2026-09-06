@props([
    'icone',
    'titulo',
    'organizacao',
    'status',        // 'pendente' | 'aprovado' | 'recusado'
    'tags' => [],     // array de strings simples: ['📅 ...', '📍 ...']
    'mensagem' => null,
    'idEvento' => null,
    'xp' => null,
    'termoUrl' => null,      // se aprovado e tiver termo em PDF
    'motivoRecusa' => null,  // se recusado
    'idCandidatura' => null, // habilita botão "Desistir" quando pendente
    'dataRodape' => null,
    'idsEventosSimilares' => null, // habilita "Ver eventos similares" quando recusado
])

@php
    $statusConfig = [
        'pendente' => ['emoji' => '⏳', 'label' => 'Pendente'],
        'aprovado' => ['emoji' => '✅', 'label' => 'Aprovado'],
        'recusado' => ['emoji' => '❌', 'label' => 'Recusado'],
    ][$status];
@endphp

<div {{ $attributes->class(['card-candidatura-item', $status]) }}>

    <div class="card-cand-topo">
        <div class="card-cand-icone">{{ $icone }}</div>
        <div class="card-cand-info">
            <strong>{{ $titulo }}</strong>
            <p>{{ $organizacao }}</p>
            <div class="card-cand-tags">
                <span class="tag-status {{ $status }}">{{ $statusConfig['emoji'] }} {{ $statusConfig['label'] }}</span>
                @foreach ($tags as $tag)
                    <span class="tag-meta">{{ $tag }}</span>
                @endforeach
                @if ($xp)
                    <span class="tag-xp">+{{ $xp }} XP</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Mensagem de motivação (candidaturas pendentes) --}}
    @if ($mensagem)
        <div class="card-cand-mensagem bg-[--color-bg-base] border border-gray-200 rounded-xl p-5 flex flex-col gap-3">
            <p class="card-cand-mensagem-label">Sua mensagem</p>
            <p class="card-cand-mensagem-texto">"{{ $mensagem }}"</p>
        </div>
    @endif

    {{-- Termo de participação (candidaturas aprovadas e concluídas) --}}
    @if ($termoUrl)
        <div class="card-cand-termo bg-verde-100 border border-[#c8e0d4] rounded-xl">
            <span>📄 <strong>Termo de Participação disponível</strong> — comprove sua atuação voluntária</span>
            <a href="{{ $termoUrl }}" class="btn-sm btn-outline-verde">⬇ Baixar PDF</a>
        </div>
    @endif

    {{-- Motivo da recusa --}}
    @if ($motivoRecusa)
        <div class="card-cand-motivo">
            <p class="motivo-label">Motivo da recusa:</p>
            <p class="motivo-texto">{{ $motivoRecusa }}</p>
        </div>
    @endif

    <div class="card-cand-rodape">
        <span class="card-cand-data">{{ $dataRodape }}</span>
        <div class="card-cand-acoes">
            @if ($idEvento)
                <a href="/evento/{{ $idEvento }}" class="btn-secundario btn-sm">Ver evento</a>
            @endif

            @if ($status === 'pendente' && $idCandidatura)
                <a href="/candidatura/{{ $idCandidatura }}/desistir" class="btn-declined btn-sm">Desistir</a>
            @endif

            @if ($status === 'recusado' && $idsEventosSimilares)
                <a href="/feed?similar={{ $idsEventosSimilares }}" class="btn-dourado btn-sm">Ver eventos similares →</a>
            @endif
        </div>
    </div>

</div>