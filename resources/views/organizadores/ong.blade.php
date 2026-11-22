@extends('layouts.organizer')
@section('title','Organizadores da ONG')
@section('breadcrumb')<x-breadcrumb-ong :itens="[['label'=>'Organizadores']]" />@endsection
@section('content')
<section class="org-card"><div class="org-identity"><div><h1 class="org-name">Organizadores da ONG</h1><p>{{ $work['organizations'][0]['nome_fantasia'] }} · Convites, vínculos e responsabilidades.</p></div></div></section>
<x-organizer-links :work="$work" :institution="true" />
<x-organizer-responsibilities :work="$work" />
@endsection
