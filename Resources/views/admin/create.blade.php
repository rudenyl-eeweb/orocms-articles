@extends($cplayout)
@section('title'){{ trans('articles::articles.admin.form.create.header') }}@stop

@section('content')
    <div>
        @include('articles::admin.form', [
            'header' => trans('articles::articles.admin.form.create.header'),
            'statuses' => [
                0 => 'Disabled',
                1 => 'Enabled'
            ]
        ])
    </div>
@stop
