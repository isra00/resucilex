<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function get()
    {
        $total = DB::scalar(
            'SELECT COUNT(DISTINCT lemma) FROM lemma WHERE id_lang = ?',
            [app('id_lang')]
        );

        return view('home', [
            'total' => $total,
            'bodyClass' => 'home',
            'hrefLangs' => app('currentRouteWithAllLocales'),
        ]);
    }
}
