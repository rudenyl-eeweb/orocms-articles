<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">
            {{ $header or 'Article Form' }}
        </h1>
        <ol class="breadcrumb">
            <li class="active">
                <i class="fa fa-dashboard"></i> <a href="{{ route('admin.dashboard') }}">{{ trans('admin.dashboard.header') }}</a>
            </li>
            <li class="active">
                {{ trans('articles::articles.admin.header') }}
            </li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        @if(isset($model))
        {!! Form::model($model, [
            'class' => 'form-default', 
            'method' => 'PUT', 
            'files' => true, 
            'route' => [
                'admin.modules.articles.update', $model->id
            ]
        ]) !!}
        @else
        {!! Form::open([
            'class' => 'form-default', 
            'files' => true, 
            'route' => 'admin.modules.articles.store'
        ]) !!}
        @endif
            <div class="form-group">
                {!! Form::label('title', trans('articles::articles.admin.form.label.title')) !!}
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
                {!! $errors->first('title', '<div class="text-danger">:message</div>') !!}
            </div>
            <div class="form-group">
                {!! Form::label('slug', trans('articles::articles.admin.form.label.slug')) !!}
                {!! Form::text('slug', null, ['class' => 'form-control']) !!}
                {!! $errors->first('slug', '<div class="text-danger">:message</div>') !!}
            </div>
            <div class="form-group">
                {!! Form::label('description', trans('articles::articles.admin.form.label.description')) !!}
                {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
                {!! $errors->first('description', '<div class="text-danger">:message</div>') !!}
            </div>
            <div class="form-group">
                {!! Form::label('published', trans('articles::articles.admin.form.label.published')) !!}
                {!! Form::select('published', $statuses, null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group">
                {!! Form::submit( trans('admin.user.form.button.' . (isset($model) ? 'update' : 'save')), [
                    'class' => 'btn btn-primary form-button', 
                    'role' => 'form-button'
                ]) !!}
                <span class="lg hidden-xs">
                    <a href="{!! route('admin.modules.articles.index') !!}">
                        @if(isset($model))
                        {{ trans('articles::articles.admin.form.button.close') }}
                        @else
                        {{ trans('articles::articles.admin.form.button.cancel') }}
                        @endif
                    </a>
                </span>
                <a href="{!! route('admin.modules.articles.index') !!}" class="btn btn-default btn-block visible-xs ">
                    {{ trans('articles::articles.admin.form.button.cancel') }}
                </a>
            </div>

        {!! Form::close() !!}
    </div>
</div>


