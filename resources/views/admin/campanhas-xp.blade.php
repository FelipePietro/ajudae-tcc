@extends('layouts.admin')

@section('content')
    @include('admin.partials.queue', [
        'description' => 'Campanhas temáticas de XP.',
        'queueLabel' => 'Campanhas cadastradas',

    ])
@endsection
