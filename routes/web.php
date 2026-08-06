<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';



Route::get('/validate-internship/{id}', function ($id) {
    $pengajuan = \App\Models\PengajuanMagang::findOrFail($id);
    return view('validate-internship', ['pengajuan' => $pengajuan]);
})->name('validate-internship');

Route::get('/validate-certificate/{id}', function ($id) {
    $pengajuan = \App\Models\PengajuanMagang::findOrFail($id);
    return view('validate-certificate', ['pengajuan' => $pengajuan]);
})->name('validate-certificate');
