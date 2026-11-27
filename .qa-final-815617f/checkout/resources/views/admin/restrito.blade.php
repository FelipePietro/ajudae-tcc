@extends('layouts.admin')

@section('content')
    <header class="fila-cabecalho"><div><h1>Acesso restrito</h1><p>Seu perfil não tem permissão para consultar esta área.</p></div></header>
    <x-botao-secundario :href="$admin ? route('admin.admin', $params) : \App\Support\NavigationContext::homeUrl()" texto="Voltar ao início" />
@endsection
