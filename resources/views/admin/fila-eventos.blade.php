@extends('layouts.admin')

@section('content')
    @include('admin.partials.queue', ['description' => 'Consulte a aprovação inicial, a reanálise e o histórico de Eventos.'])
@endsection
