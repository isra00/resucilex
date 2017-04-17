<?php

namespace Resucilex\Controllers;

class ViewList
{
	public function getList(\Silex\Application $app)
	{
		$words = $this->fetchWords($app);

		foreach ($words as &$word)
		{
			$word['firstLetter'] = iconv("utf-8", "ascii//TRANSLIT", mb_substr($word['word'], 0, 1));
		}

		return $app['twig']->render('list.twig', ['words' => $words]);
	}

	public function getCloud(\Silex\Application $app)
	{
		$words = $this->fetchWords($app);

		$colors = ['d22e2e', 'fe9700', 'fe5621', '3e50b4', '009587', '785447', '8ac249', '5f7c8a', '9b26af'];

		$max = max(array_column($words, 'occurences'));

		foreach ($words as $i=>&$word)
		{
			$word['color'] = $colors[$i % count($colors)];
			$word['size'] = ($word['occurences'] / $max) * 100 + 12;
			$word['opacity'] = $word['occurences'] > 2 ? 1 : .5;
			
			if ($word['occurences'] > 2)
			{
				$word['size'] += 1/(log($word['occurences']) + 0.00001) * 3;
			}

			//echo "<pre>" . $word['word'] . "\t" . $word['occurences'] . "\t" . round($word['size'], 2) . "\t" . round(log($word['occurences']), 2) . "</pre>\n";
		}

		return $app['twig']->render('cloud.twig', ['words' => $words, 'bodyClass' => 'page-cloud full-width']);
	}

	protected function fetchWords(\Silex\Application $app)
	{
		$sql = <<<SQL
SELECT lemma.lemma, SUM(word_occurences) occurences
FROM lemma
JOIN (
	select word_song.word word, lemma.lemma, COUNT(word) word_occurences 
	FROM word_song
	JOIN song USING (id_song)
	JOIN lang USING (id_lang)
	JOIN lemma USING (word)
	WHERE lang.short = ?
	GROUP BY word
	ORDER BY word
) words ON words.word = lemma.word
JOIN lang USING (id_lang)
WHERE lang.short = ?
GROUP BY lemma
ORDER BY lemma
SQL;
		$app['db']->exec("SET sql_mode = ''");
		$words = $app['db']->fetchAll($sql, [$app['locale'], $app['locale']]);

		if (!$words)
		{
			$app->abort(404, "The language does not exist");
		}

		foreach ($words as &$word)
		{
			$word['word'] = $word['lemma'];
		}

		return $words;
	}
}