<?php

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
