@extends('admin::layouts.master')
@section('title'){{ trans('articles::articles.admin.form.edit.header') }}@stop

@section('content')
    <div>
        @include('articles::admin.form', [
            'model' => $article, 
            'header' => trans('articles::articles.admin.form.edit.header'),
            'statuses' => [
                0 => 'Disabled',
                1 => 'Enabled'
            ]
        ])
    </div>
@stop
