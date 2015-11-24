<?php
/**
 * Routes
 */
Route::group(['namespace' => 'Modules\Articles\Http\Controllers'], function() {
    Route::group(['prefix' => 'articles'], function() {
        Route::get('/', ['as' => 'articles.index', 'uses' => 'ArticlesController@index']);
        Route::get('/{slug}', ['as' => 'articles.show', 'uses' => 'ArticlesController@show']);
    });

    /**
     * Admin routes
     */
    Route::group(['prefix' => 'admin/modules/articles', 'middleware' => config('admin.filter.auth')], function() {
        // Route::resource('/', AdminController::class, [
        //     'names' => [
        //         'index' => 'admin.modules.articles.index',
        //         'create' => 'admin.modules.articles.create',
        //         'store' => 'admin.modules.articles.store',
        //         'show' => 'admin.modules.articles.show',
        //         'update' => 'admin.modules.articles.update',
        //         'edit' => 'admin.modules.articles.edit',
        //         'destroy' => 'admin.modules.articles.destroy'
        //     ]
        // ]);
        
        Route::get('/', ['as' => 'admin.modules.articles.index', 'uses' => 'AdminController@index']);
        Route::get('/create', ['as' => 'admin.modules.articles.create', 'uses' => 'AdminController@create']);
        Route::get('/{article}/edit', ['as' => 'admin.modules.articles.edit', 'uses' => 'AdminController@edit']);
        Route::post('/', ['as' => 'admin.modules.articles.store', 'uses' => 'AdminController@store']);
        Route::put('/{article}', ['as' => 'admin.modules.articles.update', 'uses' => 'AdminController@update']);
        Route::delete('/{article}', ['as' => 'admin.modules.articles.destroy', 'uses' => 'AdminController@destroy']);
    });
});
