@extends('admin::layouts.master')
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
    <div class="list-container">
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
                data-data-field="data"
                data-page-list="[10, 20, 50, 100, ALL]"
                data-toolbar="#toolbar">
            <thead>
            <tr>
                <th data-field="state" data-checkbox="true"></th>
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
        // add popover render callback
        BT.field_callbacks.add('published', 'popover', function(row) {
            var tmpl = '<span class="popup-view" data-pos="relative"><p style="color:red;">This item has been marked as deleted. To <strong>undelete</strong> this record, click the <strong>Restore</strong> button.</p><i>Deleted At:</i><b>{deleted_at}</b><button class="edit btn btn-sm btn-primary" role="ajax" data-url="/admin/modules/articles/{id}" data-params-restore="1" data-method="PUT">Restore</button>&nbsp;<button role="confirm" data-url="/admin/modules/articles/{id}" data-title="Force Delete" data-method="DELETE" data-message="This will be gone forever. Are you really sure you want to delete this account?" data-params-force_delete="1" class="btn btn-sm btn-default">Remove Permanently</button></a></span>';
            // parse
            for (var k in row) tmpl = tmpl.replace(new RegExp('{'+k+'}', 'g'), row[k]);
            return tmpl;
        });

        // event listener
        BT.target.on('click', '.popover button', function() {
            var el = $(this);
            $('.alert').remove(), $('.popover').popover('hide');

            // events
            el.on('railed.beforeSend', function(r,s) {
                Preloader.create('.list-container', 'centered', false,true);
            })
            .on('railed.onComplete', function(e, result) {
                Preloader.clear(function() {
                    // check response
                    var success = result.success || result.status,
                        message = result.statusText || (result.message || 'No message returned.');

                    BT.notify(message, '.bootstrap-table', success===true?'info':'warning', 'insertBefore', success===true);

                    BT.target.bootstrapTable('refresh');
                });
            });     
        });
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
                return '<a href="#" data-toggle="popover" data-trigger="click" data-placement="left"><span class="label label-danger" >'
                    + '{{ trans('admin.defaults.list.status.deleted') }}' + '</span></a>';

            return '<span class="label label-{type}">{title}</span>'
                .replace(/{type}/, +value ? 'primary' : 'inactive')
                .replace(/{title}/, +value ? 
                    '{{ trans('admin.defaults.list.status.published') }}' : 
                    '{{ trans('admin.defaults.list.status.unpublished') }}'
                );
        },
    });
@endpush