<?php
/**
 * Routes
 */

Route::group(['namespace' => 'Modules\Articles\Http\Controllers'], function() {
    Route::group(['prefix' => config('modules.configs.articles.route.prefix', 'articles')], function() {
        // Route::get('/', ['as' => 'articles.index', 'uses' => 'ArticlesController@index']);
        // Route::get('/{slug}', ['as' => 'articles.show', 'uses' => 'ArticlesController@show']);
    });

    /**
     * Admin routes
     */
    Route::group([
        'prefix' => config('modules.configs.articles.route.cp', 'articles'), 
        'middleware' => config('admin.filter.auth')], function() {
        Route::get('/', ['as' => 'admin.modules.articles.index', 'uses' => 'AdminController@index']);
        Route::get('/create', ['as' => 'admin.modules.articles.create', 'uses' => 'AdminController@create']);
        Route::get('/{article}/edit', ['as' => 'admin.modules.articles.edit', 'uses' => 'AdminController@edit']);
        Route::post('/', ['as' => 'admin.modules.articles.store', 'uses' => 'AdminController@store']);
        Route::put('/{article}', ['as' => 'admin.modules.articles.update', 'uses' => 'AdminController@update']);
        Route::delete('/{article}', ['as' => 'admin.modules.articles.destroy', 'uses' => 'AdminController@destroy']);
    });
});
