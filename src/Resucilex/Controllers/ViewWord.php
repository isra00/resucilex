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
		$songs = $app['db']->fetchAll($sql, [$app['id_lang'], $word, $app['id_lang']]);

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
			'word' 				=> $songs[0]['isProper'] ? $this->mbUcFirst($word) : $word, 
			'songs' 			=> $songs, 
			'total_occurences' 	=> $totalOccurences, 
			'bodyClass' 		=> 'full-width', 
		]);
	}

	/**
	 * Copied from Twig_Extension_Core::twig_capitalize_string_filter()
	 */
	protected function mbUcFirst($string)
	{
		return mb_strtoupper(mb_substr($string, 0, 1)).mb_strtolower(mb_substr($string, 1, mb_strlen($string)));
	}
}