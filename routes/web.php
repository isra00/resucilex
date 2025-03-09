<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $autodetectedLang = request()->getPreferredLanguage(array_column(config('resucilex.lang'), 'short'));
    return redirect()->route('home', ['locale' => $autodetectedLang]);
});

$validLocales = implode('|', array_column(config('resucilex.lang'), 'short'));

Route::get('/{locale}', [\App\Http\Controllers\HomeController::class, 'get'])
    ->where('locale', $validLocales)
    ->name('home');

Route::get('/{locale}/list/{dufour?}', [\App\Http\Controllers\ViewListController::class, 'getList'])
    ->where('locale', $validLocales)
    ->where('dufour', 'dufour')
    ->name('list');

Route::get('/{locale}/tagcloud', [\App\Http\Controllers\ViewListController::class, 'getCloud'])
    ->where('locale', $validLocales)
    ->name('tagcloud');

Route::get('/{locale}/summary', [\App\Http\Controllers\ViewSummaryController::class, 'get'])
    ->where('locale', $validLocales)
    ->name('summary');

Route::get('/{locale}/{word}', [\App\Http\Controllers\ViewWordController::class, 'get'])
    ->where('locale', $validLocales)
    ->name('word');

Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'get']);