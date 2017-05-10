<?php

namespace Resucilex\Controllers;

class ViewList
{
	public function getList(\Silex\Application $app, $dufour=false)
	{
		$words = $this->fetchWords($app, $dufour);

		foreach ($words as &$word)
		{
			$word['firstLetter'] = iconv(
				"utf-8", 
				"ascii//TRANSLIT", 
				mb_substr($word['word'], 0, 1)
			);
		}

		return $app['twig']->render('list.twig', [
			'words' 	=> $words, 
			'pageTitle' => $app['translator']->trans('All words'),
			'dufour' 	=> $dufour
		]);
	}

	public function getCloud(\Silex\Application $app)
	{
		$words = $this->fetchWords($app);

		$colors = ['d22e2e', 'fe9700', 'fe5621', '3e50b4', '009587', '785447', '8ac249', '5f7c8a', '9b26af'];

		$max = max(array_column($words, 'occurences'));

		foreach ($words as $i=>&$word)
		{
			$word['color'] 	 = $colors[$i % count($colors)];
			$word['size'] 	 = ($word['occurences'] / $max) * 100 + 12;
			$word['opacity'] = $word['occurences'] > 2 ? 1 : .5;
			
			if ($word['occurences'] > 2)
			{
				$word['size'] += 1/(log($word['occurences']) + 0.00001) * 3;
			}
		}

		return $app['twig']->render('cloud.twig', [
			'words' 	=> $words, 
			'bodyClass' => 'page-cloud full-width',
			'pageTitle' => 'Word cloud'
		]);
	}

	protected function fetchWords(\Silex\Application $app, $dufour=false)
	{
		$sql = <<<SQL
SELECT lemma.lemma, isProper, SUM(word_occurences) occurences
FROM lemma
JOIN (
	select word_song.word word, lemma.lemma, COUNT(word) word_occurences 
	FROM word_song
	JOIN song USING (id_song)
	JOIN lemma USING (word)
	WHERE song.id_lang = ? AND lemma.id_lang = ?
	GROUP BY word
	ORDER BY word
) words ON words.word = lemma.word
#
WHERE lemma.id_lang = ?
GROUP BY lemma
ORDER BY lemma.lemma COLLATE utf8_general_ci
SQL;

		$app['db']->exec("SET sql_mode = ''");
		
		$sql = str_replace(
			'#', 
			$dufour ? 'JOIN dufour ON dufour.lemma = lemma.lemma AND dufour.id_lang = ' . $app['id_lang'] : '', 
			$sql
		);

		$words = $app['db']->fetchAll(
			$sql, 
			[$app['id_lang'], $app['id_lang'], $app['id_lang']]
		);

		if (!$words)
		{
			$app->abort(404, "No words found");
		}

		foreach ($words as &$word)
		{
			$word['word'] = $word['lemma'];
		}

		return $words;
	}
}