<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\FreeInvoiceController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');



Route::middleware(['auth'])
    ->group(function () {
        Route::get('/invoices/{slug}/print', [InvoiceController::class, 'printInvoice'])
            ->name('invoices.print');

        Route::get('/reservations/invoices/{slug}/print', [InvoiceController::class, 'printReservationInvoice'])
            ->name('reservations.invoices.print');

        Route::get('/free-invoices/{freeInvoice}/print', [FreeInvoiceController::class, 'print'])
            ->name('free-invoices.print');
    });

    Route::get('/invoices/{slug}/{token}', [InvoiceController::class, 'publicView'])
    ->name('invoices.publicView');


Route::get('/', function () {
    abort_unless(auth()->check(), 403, 'Unauthorized access');
})->name('login');
    