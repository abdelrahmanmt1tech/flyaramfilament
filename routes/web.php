<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');


Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'printInvoice'])->name('invoices.print');


