@extends('layouts.admin')

@section('content')
    @isset($integratedAdmin)
        @include('admin.partials.web-event-moderation')
    @else
        @include('admin.partials.queue', ['description' => 'Consulte a aprovação inicial, a reanálise e o histórico de Eventos.'])
    @endisset
@endsection
