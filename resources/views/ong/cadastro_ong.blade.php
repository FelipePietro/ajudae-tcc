@extends('layouts.account')
@section('title', 'Cadastro de ONG')
@section('content')
@if(config('identity.enabled'))
    @php
        $sections = \App\Support\VisualOneFixtures::sections('ong');
        foreach (array_slice($sections, 0, 3, true) as $index => $section) {
            foreach ($section['fields'] as $position => $field) {
                $limit = match ($field['key']) {
                    'nome_fantasia', 'email_ong', 'logradouro_ong', 'bairro_ong', 'cidade_ong',
                    'nome_responsavel_ong', 'email_responsavel_ong' => 100,
                    'descricao_ong' => 256,
                    'tel_ong', 'tel_responsavel_ong' => 15,
                    default => null,
                };
                if ($limit !== null) {
                    $sections[$index]['fields'][$position]['max'] = $limit;
                }
            }
        }
        $sections[3]['intro'] = 'Envie o documento de identidade do responsável pela organização.';
        $sections[4]['intro'] = 'Escolha o login e a senha da conta institucional.';
    @endphp
    <x-account-wizard :sections="$sections" integrated integrated-actor="ong" />
@else
    <x-demo-scenarios :demo="$demo" />
    <x-account-wizard :demo="$demo" :sections="$sections" />
@endif
@endsection
