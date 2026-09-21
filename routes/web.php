<?php

use App\Http\Controllers\InvoicePrintController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware('auth:admin')->prefix('invoices')->name('invoices.')->group(function () {
    Route::get('{invoice}/print', [InvoicePrintController::class, 'html'])->name('print');
});
