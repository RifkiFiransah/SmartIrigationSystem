<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome-modular');
});

Route::get('/modular', function () {
    return view('welcome');
});

// Dashboard routes
Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/irrigation-lines', function () {
    return view('irrigation-lines-dashboard');
});