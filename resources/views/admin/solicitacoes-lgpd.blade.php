@extends('layouts.admin')
@section('title', $title)

@section('content')
@isset($integratedAdmin)
    @include('admin.integrated-entry')
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
