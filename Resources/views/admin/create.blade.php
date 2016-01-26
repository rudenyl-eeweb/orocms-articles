@extends('admin::layouts.master')
@section('title'){{ trans('articles::articles.admin.form.create.header') }}@stop

@section('content')
    <div>
        @include('articles::admin.form', [
            'header' => trans('articles::articles.admin.form.create.header')
        ])
    </div>
@stop
