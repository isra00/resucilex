<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $acceptLang = request()->getPreferredLanguage(array_column(config('resucilex.lang'), 'short'));
    return redirect()->route('home', ['locale' => $acceptLang]);
});
$validLocales = implode('|', array_column(config('resucilex.lang'), 'short'));

Route::get('/{locale}', function (Request $request, $locale) {
    $total = DB::scalar(
		'SELECT COUNT(DISTINCT lemma) FROM lemma WHERE id_lang = ?',
		[app('id_lang')]
	);

    return view('home', [
        'total' => $total,
        'bodyClass' => 'home',
        'hrefLangs' => app('currentRouteWithAllLocales'),
    ]);
})->where('locale', $validLocales)->name('home');

Route::get('/{locale}/list/{dufour?}', [ViewListController::class, 'getList'])
    ->where('locale', $validLocales)
    ->name('list');

Route::get('/{locale}/summary', [ViewSummaryController::class, 'get'])
    ->where('locale', $validLocales)
    ->name('summary');

Route::get('/{locale}/tagcloud', [ViewListController::class, 'getCloud'])
    ->where('locale', $validLocales)
    ->name('tagcloud');

Route::get('/{locale}/{word}', [ViewWordController::class, 'get'])
    ->where('locale', $validLocales)
    ->name('word');

Route::get('/sitemap.xml', [SitemapController::class, 'get']);