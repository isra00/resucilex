<?php

namespace Resucilex\Controllers;

class ViewSummary
{
	public function get(\Silex\Application $app)
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
		$app['db']->exec("SET sql_mode = ''");
		
		$wordsSongs = $app['db']->fetchAll(
			$sql, 
			[$app['id_lang'], $app['id_lang']]
		);

		if (!$wordsSongs)
		{
			$app->abort(404, "The language does not exist");
		}

		foreach ($wordsSongs as &$wordSong)
		{
			$wordSong['occurences'] = preg_match_all(
				"/([^\w])(" . $wordSong['word'] . "|" . mb_strtoupper($wordSong['word']) . ")([^\w])/i", 
				$wordSong['text']
			);

			if ($wordSong['isProper'])
			{
				$wordSong['lemma'] = $app['mbUcFirst']($wordSong['lemma']);
			}
		}

		return $app['twig']->render('summary.twig', [
			'wordsSongs' => $wordsSongs,
			'pageTitle'	 => $app['translator']->trans('Integrated view'),
			'hrefLangs'  => $app['currentRouteWithAllLocales'],
		]);
	}
}