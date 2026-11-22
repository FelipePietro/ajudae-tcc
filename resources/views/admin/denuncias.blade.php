@extends('layouts.admin')

@section('content')
    @include('admin.partials.queue', ['description' => 'Consulte ocorrências de participação e cancelamento.'])
@endsection
