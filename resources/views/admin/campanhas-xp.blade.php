@extends('layouts.admin')

@section('content')
    @include('admin.partials.queue', [
        'description' => 'Campanhas temáticas de XP.',
        'queueLabel' => 'Campanhas cadastradas',
        'queueMessage' => 'A consulta e a gestão de campanhas ainda estão indisponíveis.',
    ])
@endsection
