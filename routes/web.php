<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/11', function () {
    return ['Laravel' => '11111'];
});

require __DIR__.'/auth.php';
