<?php

Route::group(['middleware' => 'cors', 'namespace' => '\Yashon\Laravel\Core'], function () {
    Route::get('api/debug', 'Debuger@getLog');
});