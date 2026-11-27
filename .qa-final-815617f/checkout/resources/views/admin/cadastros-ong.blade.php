@extends('layouts.admin')

@section('content')
    @include('admin.partials.queue', ['description' => 'Consulte os dados cadastrais e decida sobre a aprovação da organização.'])
@endsection
