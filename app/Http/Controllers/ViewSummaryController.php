<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ViewSummaryController extends \Illuminate\Routing\Controller
{
	public function get($locale)
	{
		$sql = <<<SQL
select lemma.lemma, lemma.isProper, word_song.word, song.title, song.text
FROM word_song
JOIN song USING (id_song)
JOIN lemma ON word_song.word = lemma.word AND lemma.id_lang = ?
WHERE song.id_lang = ?
GROUP BY lemma, id_song
ORDER BY lemma COLLATE utf8_general_ci, song.title COLLATE utf8_general_ci
SQL;

		$wordsSongs = DB::select(
			$sql, 
			[app('id_lang'), app('id_lang')]
		);

		if (!$wordsSongs)
		{
			abort(404, "The language does not exist");
		}

		foreach ($wordsSongs as &$wordSong)
		{
			$wordSong->occurences = preg_match_all(
				"/([^\w])(" . $wordSong->word . "|" . mb_strtoupper($wordSong->word) . ")([^\w])/i", 
				$wordSong->text
			);

			if ($wordSong->isProper)
			{
				$wordSong->lemma = app('mbUcFirst')($wordSong->lemma);
			}
		}

		return view('summary', [
			'wordsSongs' => $wordsSongs,
			'pageTitle'	 => __('Integrated view'),
			'hrefLangs'  => app('currentRouteWithAllLocales'),
		]);
	}
}