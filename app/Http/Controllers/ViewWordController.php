<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ViewWordController extends \Illuminate\Routing\Controller
{
	/**
	 * Known issue: This algorithm, as it is, selects one word per lemma per 
	 * song. So if it happens that one song has two words under the same lemma, 
	 * only one word will be counted and highlighted.
	 */
	public function get($locale, $word)
	{
		$sql = <<<SQL
SELECT lemma.lemma, pos_code.description posCode, lemma.isProper, word_song.word, song.*
FROM word_song
JOIN song USING (id_song)
JOIN lemma USING (word)
LEFT JOIN pos_code ON posTagging = code
WHERE song.id_lang = ?
AND lemma = ? AND lemma.id_lang = ?
GROUP BY id_song
ORDER BY page
SQL;
		$songs = DB::select(
			$sql, 
			[app('id_lang'), $word, app('id_lang')]
		);

		if (!$songs)
		{
			abort(404, "The word you are looking for is not in the index.");
		}

		$totalOccurences = 0;

		foreach ($songs as &$song)
		{
			//Regexp does not highlight words if first or last in text. This solves it.
			$song->text = '#' . $song->text . '#';

			$song->text = preg_replace(
				"/([^\w])(" . $song->word . "|" . mb_strtoupper($song->word) . ")([^\w])/is",
				"$1<strong>$2</strong>$3", 
				$song->text, 
				-1, 
				$occurences
			);

			//Remove the initial and ending # added above.
			$song->text = mb_substr(
				$song->text, 
				1, 
				mb_strlen($song->text) - 2
			);

			$song->occurences = $occurences;

			$totalOccurences += $occurences;
		}

		$word4print = $songs[0]->isProper ? app('mbUcFirst')($word) : $word;

		$relateds = DB::select(
			"SELECT DISTINCT related.lemma, related.related FROM related JOIN lemma ON related.related = lemma.lemma AND related.id_lang = lemma.id_lang WHERE related.lemma = ? AND related.id_lang = ?",
			[$word, app('id_lang')]
		);

		$relateds = array_column($relateds, 'related');

		return view('word', [
			'word' 				=> $word4print, 
			'songs' 			=> $songs, 
			'total_occurences' 	=> $totalOccurences, 
			'relateds' 			=> $relateds, 
			'bodyClass' 		=> 'full-width', 
			'pageTitle'			=> $word4print,
			'noFooter'			=> true
		]);
	}
}