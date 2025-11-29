<?php

use Illuminate\Support\Facades\Route;
use Laikmosh\Plog\Http\Controllers\PlogController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/logs', [PlogController::class, 'index'])->name('plog.index');
});