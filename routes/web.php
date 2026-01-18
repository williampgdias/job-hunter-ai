<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\JobHunterController;


Route::get('/', function () {
    return view('tester');
});

Route::get('/letter/{uuid}', [JobHunterController::class, 'showPublic'])->name('public.letter');