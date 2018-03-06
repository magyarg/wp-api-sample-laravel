<?php

Route::group(['prefix' => '/'], function() {
    Route::get('/custom', 'FrontendController@customQuery');
    Route::get('/', 'FrontendController@index');
    Route::get('/{slug}', 'FrontendController@show');
});
