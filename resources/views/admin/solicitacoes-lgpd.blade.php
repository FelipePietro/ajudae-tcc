@extends('layouts.admin')
@section('title', $title)

@section('content')
@isset($integratedAdmin)
    @php
        $permissions = \App\Support\WebNavigation::for(request())->adminPermissions;
        $canRead = isset($permissions['lgpd.visualizar']);
        $canRespond = isset($permissions['lgpd.responder']);
        $description = match (true) {
            $canRead && $canRespond => 'Esta conta tem permissão para consultar e responder solicitações de privacidade.',
            $canRespond => 'Esta conta tem permissão para responder solicitações de privacidade.',
            default => 'Esta conta tem permissão para consultar solicitações de privacidade.',
        };
    @endphp
    @include('admin.partials.queue', [
        'description' => $description,
        'queueLabel' => 'Solicitações de titulares',
        'queueMessage' => $page === 'responder-lgpd' ? 'A resposta a solicitações ainda está indisponível.' : 'A fila de solicitações ainda está indisponível.',
    ])
    @if($canRespond && $page === 'solicitacoes-lgpd')
        <x-botao-secundario :href="route('admin.responder-lgpd')" class="mt-4">Área de resposta</x-botao-secundario>
    @endif
    <x-botao-secundario :href="route('admin.admin')" class="mt-4">Voltar ao painel</x-botao-secundario>
@else
    @include('admin.partials.queue', ['description' => 'Acompanhe o andamento e a resposta às solicitações de titulares.'])
    <section data-privacy-root>
        <script type="application/json" data-privacy-config>{!! json_encode(\App\Support\PrivacyFixtures::configuration(false, true), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
        <p class="admin-widget">Revisões de pedidos e histórico de atendimento. Nenhuma solicitação ou exportação é efetivada por esta revisão. {{ $variant === 'privacidade' ? 'Permissão: consulta, sem capacidade de resposta.' : 'Permissões: consulta e resposta.' }}</p>
        <p data-privacy-feedback role="status" tabindex="-1"></p>
        <x-privacy-requests />
    </section>

@endisset
@endsection
