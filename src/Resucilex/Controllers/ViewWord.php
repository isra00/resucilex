<?php

namespace Resucilex\Controllers;

class ViewWord
{
	/**
	 * Known issue: This algorithm, as it is, selects one word per lemma per 
	 * song. So if it happens that one song has two words under the same lemma, 
	 * only one word will be counted and highlighted.
	 */
	public function get(\Silex\Application $app, $word)
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
		$app['db']->exec("SET sql_mode = ''");
		
		$songs = $app['db']->fetchAll(
			$sql, 
			[$app['id_lang'], $word, $app['id_lang']]
		);

		if (!$songs)
		{
			$app->abort(404, "The word you are looking for is not in the index.");
		}

		$totalOccurences = 0;

		foreach ($songs as &$song)
		{
			//Regexp does not highlight words if first or last in text. This solves it.
			$song['text'] = '#' . $song['text'] . '#';

			$song['text'] = preg_replace(
				"/([^\w])(" . $song['word'] . "|" . mb_strtoupper($song['word']) . ")([^\w])/is", 
				"$1<strong>$2</strong>$3", 
				$song['text'], 
				-1, 
				$occurences
			);

			//Remove the initial and ending # added above.
			$song['text'] = mb_substr(
				$song['text'], 
				1, 
				mb_strlen($song['text']) - 2
			);

			$song['occurences'] = $occurences;

			$totalOccurences += $occurences;
		}

		$word4print = $songs[0]['isProper'] ? $app['mbUcFirst']($word) : $word;

		$relateds = $app['db']->fetchAll(
			"SELECT DISTINCT related.lemma, related.related FROM related JOIN lemma ON related.related = lemma.lemma AND related.id_lang = lemma.id_lang WHERE related.lemma = ? AND related.id_lang = ?",
			[$word, $app['id_lang']]
		);

		$relateds = array_column($relateds, 'related');

		return $app['twig']->render('word.twig', [
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