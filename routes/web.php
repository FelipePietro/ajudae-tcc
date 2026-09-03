<?php

use Illuminate\Support\Facades\Route;

Route::get('/teste', function () {
    return view('teste');
});

Route::get('/admin', function () {
    return view('admin');
});
