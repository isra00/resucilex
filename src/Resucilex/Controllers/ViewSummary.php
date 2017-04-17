<?php

namespace Resucilex\Controllers;

class ViewSummary
{
	public function get(\Silex\Application $app)
	{
		$sql = <<<SQL
select word_song.word word, song.title, song.page, song.text
FROM word_song
JOIN song USING (id_song)
JOIN lang USING (id_lang)
WHERE lang.short = ?
ORDER BY word, song.title
SQL;

		$wordsSongs = $app['db']->fetchAll($sql, [$app['locale']]);

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
		}

		return $app['twig']->render('summary.twig', ['wordsSongs' => $wordsSongs]);
	}
}