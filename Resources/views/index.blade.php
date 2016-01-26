@extends('theme::base_layout')

@if (isset($article))
    @section('title'){{ $article->title }}@stop

    @section('content')
        <h1>
            {{ $article->title }}
        </h1>

        <div role="md">
            {!! $article->description !!}
        </div>
    @stop
@endif
