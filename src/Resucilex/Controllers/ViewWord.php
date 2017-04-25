<?php

namespace Resucilex\Controllers;

class ViewWord
{
	public function get(\Silex\Application $app, $word)
	{
		$sql = <<<SQL
SELECT lemma.lemma, lemma.isProper, word_song.word, song.*
FROM word_song
JOIN song USING (id_song)
JOIN lemma USING (word)
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

		return $app['twig']->render('word.twig', [
			'word' 				=> $word4print, 
			'songs' 			=> $songs, 
			'total_occurences' 	=> $totalOccurences, 
			'bodyClass' 		=> 'full-width', 
			'pageTitle'			=> $word4print,
			'noFooter'			=> true
		]);
	}
}