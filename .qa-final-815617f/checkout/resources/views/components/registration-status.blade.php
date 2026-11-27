@props(['demo' => null, 'status' => null, 'tone' => null, 'description' => null])
@php
    [$status, $tone, $description] = $demo['statusMeta'] ?? [$status, $tone, $description];
@endphp
<section class="account-status {{ $tone }}"><div><span class="account-eyebrow">Situação cadastral</span><h2>{{ $status }}</h2><p>{{ $description }}</p></div><span class="account-status-symbol" aria-hidden="true">{{ $tone === 'success' ? '✓' : '◷' }}</span></section>
@if($demo)
@if($demo['reason'])<x-feedback kind="error"><strong>O que revisar</strong><p>{{ $demo['reason'] }}</p></x-feedback>@endif
@if($demo['blocked'])<div class="account-note danger"><strong>Reenvio temporariamente bloqueado.</strong><p>Três reprovações em 30 dias geram bloqueio de 7 dias. Aguarde o fim do período para solicitar nova análise.</p></div>@endif
@if($demo['ongCorrectionGap'])<div class="account-note"><strong>Revise as informações da organização</strong><p>Você pode conferir os dados cadastrais. A reapresentação de documentos e a alteração da situação não estão disponíveis nesta página.</p><x-botao-secundario :href="route('ong.perfil.editar', ['cenario'=>'correcao'])">Revisar informações →</x-botao-secundario></div>@endif
@if($demo['canResubmit'])<x-botao-primario as="button" :href="null" type="button" data-demo-resubmit>Revisar envio para análise</x-botao-primario><p data-resubmit-feedback role="status" class="account-hint"></p>@endif
@if($demo['terminal'])<p class="account-hint">O cadastro não permite novo envio para análise.</p>@endif
@endif
