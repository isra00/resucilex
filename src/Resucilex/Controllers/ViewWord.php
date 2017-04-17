<?php

namespace Resucilex\Controllers;

class ViewWord
{
	public function get(\Silex\Application $app, $word)
	{
		$sql = <<<SQL
SELECT lemma.lemma, word_song.word, song.*
FROM word_song
JOIN song USING (id_song)
JOIN lang USING (id_lang)
JOIN lemma USING (word)
WHERE lang.short = ?
AND lemma = ?
ORDER BY page
SQL;

		$songs = $app['db']->fetchAll($sql, [$app['locale'], $word]);

		if (!$songs)
		{
			$app->abort(404, "The word you have written does not appear in the songbook even once.");
		}

		$totalOccurences = 0;

		foreach ($songs as &$song)
		{
			$song['text'] = preg_replace(
				"/([^\w])(" . $song['word'] . "|" . mb_strtoupper($song['word']) . ")([^\w])/is", 
				"$1<strong>$2</strong>$3", 
				$song['text'], 
				-1, 
				$occurences
			);

			$song['occurences'] = $occurences;

			$totalOccurences += $occurences;
		}

		return $app['twig']->render('word.twig', [
			'word' 				=> $word, 
			'songs' 			=> $songs, 
			'total_occurences' 	=> $totalOccurences, 
			'bodyClass' 		=> 'full-width', 
		]);
	}
}