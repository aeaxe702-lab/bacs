<?php

use think\facade\Route;
use app\middleware\CheckUID;

Route::group(function () {
    Route::post('ad','Ad/index');
    Route::post('node/stat', 'NodeStat/report')->withoutMiddleware([CheckUID::class]);
    Route::get('app/config', 'app_config/config')->withoutMiddleware([CheckUID::class]);

    Route::get('chat/history', 'feedback/index');
    Route::post('chat/send', 'feedback/save');

    Route::post('user/login', 'user/login')->withoutMiddleware([CheckUID::class]);

    Route::get('server', 'line/index');

    Route::get('text/notice', 'text/notice');
})->middleware([CheckUID::class]);
