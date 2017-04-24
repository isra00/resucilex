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
ORDER BY lemma, song.title
SQL;
		$app['db']->exec("SET sql_mode = ''");
		$wordsSongs = $app['db']->fetchAll($sql, [$app['id_lang'], $app['id_lang']]);

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
				$wordSong['lemma'] = $this->mbUcFirst($wordSong['lemma']);
			}
		}

		return $app['twig']->render('summary.twig', ['wordsSongs' => $wordsSongs]);
	}

	/**
	 * Copied from Twig_Extension_Core::twig_capitalize_string_filter()
	 * @todo ELIMINAR ESTA DUPLICIDAD DE CÓDIGO (ViewWord)
	 */
	protected function mbUcFirst($string)
	{
		return mb_strtoupper(mb_substr($string, 0, 1)).mb_strtolower(mb_substr($string, 1, mb_strlen($string)));
	}
}