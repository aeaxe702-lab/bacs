<?php

use think\facade\Route;

Route::group(function () {
    Route::get('auth/login', 'auth/login');
    Route::post('auth/doLogin', 'auth/doLogin');
    Route::get('auth/logout', 'auth/logout');
    Route::get('auth/install', 'auth/install');

    Route::get('index/index', 'index/index');

    Route::get('main/index', 'main/index');
    Route::get('main/nodeStats', 'main/nodeStatsData');

    Route::get('app/index', 'app_config/index');
    Route::post('app/add', 'app_config/add');
    Route::post('app/update', 'app_config/update');
    Route::post('app/saveConfig', 'app_config/saveConfig');
    Route::get('app/detail', 'app_config/detail');
    Route::get('app/project', 'app_config/project');
    Route::get('app/lines', 'app_config/lines');
    Route::post('app/saveLines', 'app_config/saveLines');

    Route::get('line/index', 'line/index');
    Route::post('line/add', 'line/add');
    Route::post('line/edit', 'line/edit');
    Route::post('line/destroy', 'line/destroy');
    Route::post('line/uploadImage', 'line/uploadImage');
    Route::get('line/getLine', 'line/get');
    Route::post('line/updateSort', 'line/updateSort');
    Route::get('line/nodeStats', 'line/nodeStats');

    Route::get('user/index', 'user/index');
    Route::get('user/get', 'user/get');
    Route::post('user/updateMemberExpire', 'user/updateMemberExpire');

    Route::get('feedback/index', 'feedback/index');
    Route::get('feedback/getSessions', 'feedback/getSessions');
    Route::get('feedback/getMessages', 'feedback/getMessages');
    Route::post('feedback/sendMessage', 'feedback/sendMessage');
    Route::post('feedback/toggleHidden', 'feedback/toggleHidden');

    Route::get('text/index', 'text/index');
    Route::post('text/add', 'text/add');
    Route::post('text/edit', 'text/edit');
    Route::post('text/destroy', 'text/destroy');
    Route::get('text/get', 'text/get');
})->middleware(\app\back\middleware\BackAuth::class);
