@extends($cplayout)
@section('title'){{ trans('articles::articles.admin.header') }}@stop

@section('content-header')
    <h1 class="page-header">
        {{ trans('articles::articles.admin.header') }}
    </h1>
    <ol class="breadcrumb">
        <li>
            <i class="fa fa-dashboard"></i> <a href="{{ route('admin.dashboard') }}">{{ trans('admin.menu.dashboard') }}</a>
        </li>
        <li class="active">
            {{ trans('articles::articles.admin.header') }}
        </li>
    </ol>
@stop

@section('content')
    <div id="list-container">
        <div id="toolbar" class="btn-group">
            <a role="button" href="{!! route('admin.modules.articles.create') !!}" class="btn btn-default">
                <i class="glyphicon glyphicon-file" aria-hidden="true"></i>
                <div class="hidden-xs inline-block">{{ trans('articles::articles.admin.list.button.create') }}</div>
            </a>
            <button type="button" class="btn btn-default can-toggle remove" title="{{ trans('articles::articles.admin.list.button.delete_all') }}" 
                data-url="{!! route('admin.modules.articles.destroy', csrf_token()) !!}" 
                data-method="DELETE"
                disabled>
                <i class="fa fa-trash-o"></i>
            </button>
        </div>

        <table id="table-articles"
                class="table table-no-bordered break-word"
                data-toggle="table"
                data-show-columns="true"
                data-search="true"
                data-url="{!! route('admin.modules.articles.index') !!}"
                data-pagination="true"
                data-mobile-responsive="true"
                data-check-on-init="true"
                data-striped="true"
                data-side-pagination="server"
                data-page-list="[10, 20, 50, 100, ALL]"
                data-toolbar="#toolbar">
            <thead>
            <tr>
                <th data-field="state" data-checkbox="true" data-formatter="BT.formatter.state"></th>
                <th data-field="title" data-sortable="true" data-switchable="false" data-uri="/admin/modules/articles/{id}/edit" data-formatter="BT.formatter.linkable">
                    {{ trans('articles::articles.admin.list.header.title') }}
                </th>
                <th data-field="created_at" data-width="180" data-sortable="true">
                    {{ trans('articles::articles.admin.list.header.date_added') }}
                </th>
                <th data-field="updated_at" data-width="180" data-sortable="true">
                    {{ trans('articles::articles.admin.list.header.date_modified') }}
                </th>
                <th data-field="published" data-sortable="true" data-align="center" data-width="120" data-formatter="BT.formatter.get_status">
                    {{ trans('articles::articles.admin.list.header.published') }}
                </th>
                <th data-field="id" data-align="center" data-visible="false" data-width="80" data-sortable="true">
                    {{ trans('articles::articles.admin.list.header.id') }}
                </th>
            </tr>
            </thead>
        </table>
    </div>
@stop

@push('jquery-scripts')
    // load bootstrap table
    BT.init('#table-articles', function(table) {
    });
    $.extend(BT.formatter, {
        linkable: function(value, row) {
            var uri = this.uri || false;
            if (!uri) return value;

            // get key
            var s = /{(.*?)}/.exec(uri),
                k = s ? s[1].split('|') : false,
                r = k ? k[0] : false;

            if (!r || row[r]==undefined) return value;
            var v = row[r];

            // map value
            if (typeof $[k[1]] == 'function') v = $[k[1]](v);

            return '<a class="link primary" href="' +uri.replace(s[0], v)+ '">' +value+ '</a>';
        },
        // format publishing
        get_status: function(value, row) {
            // soft deletes
            if (row.deleted_at) 
                return '<a href="#" title="{{ trans('admin.defaults.list.status.deleted') }}" data-toggle="popover" data-trigger="click" ' +
                    ' data-placement="left"><i class="fa fa-warning" style="color:red;"></a>';
            var context = '<span class="label label-{type}">{title}</span>'
                .replace(/{type}/, +value ? 'primary' : 'inactive')
                .replace(/{title}/, +value ? 
                    '{{ trans('admin.defaults.list.status.published') }}' : 
                    '{{ trans('admin.defaults.list.status.unpublished') }}'
                );
            if (+row.access) context += ' <span class="label label-default">Private</span>';
            return context;
        }
    });
@endpush
