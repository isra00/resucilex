<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SitemapController extends \Illuminate\Routing\Controller
{
	public function get()
	{
		$urls 			= [];
		$languages 		= array_column(config('resucilex.lang'), 'short');
		$urlsWithLocale = ['home', 'list', 'summary', 'tagcloud'];

		foreach ($languages as $lang)
		{
			foreach ($urlsWithLocale as $url)
			{
				$urls[] = array(
					'loc' 			=> route($url, ['locale' => $lang]),
					'priority' 		=> 1,
					'changefreq' 	=> 'weekly',
					'lastmod' 		=> date('c', $this->getLastMod($url, ['locale' => $lang]))
				);
			}
		}

		$words = DB::select('SELECT distinct lemma, id_lang FROM lemma');

		foreach ($words as $word)
		{
			$urlParams = ['locale' => config('resucilex.lang')[$word->id_lang]['short'], 'word' => $word->lemma];

			$urls[] = [
				'loc' => route('word', $urlParams),
				'priority' 		=> '0.7',
				'changefreq' 	=> 'weekly',
				'lastmod' 		=> date('c', $this->getLastMod($url, $urlParams))
            ];
		}

		return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
	}

	protected function getLastMod($url, $params)
	{
		$cachedPage = './cache' . route($url, $params) . '.html';
		return file_exists($cachedPage) ? filemtime($cachedPage) : time();
	}
}
