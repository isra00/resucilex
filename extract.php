<?php

$db = new mysqli('localhost', 'root', 'root', 'resucilex');
$db->set_charset('utf8');

/*$db->query("truncate table word_song;");
$db->query("truncate table song");*/

$languages = ['es'=>1, 'en'=>2];

$text = file_get_contents("http://localhost/resucilex/list.1");

preg_match_all('/<a href="(.*)">(.*) \(\d+\)<\/a>/i', $text, $results);
$words = $results[2];

foreach ($words as $i=>$word)
{
	$songPage = file_get_contents("http://localhost/resucilex/" . $results[1][$i]);

	$songPage = str_replace('<!doctype html>', '<?xml version="1.0" encoding="utf-8"?>', $songPage);
	$songPage = str_replace('<meta http-equiv="X-UA-Compatible" content="IE=edge">', '<meta http-equiv="X-UA-Compatible" content="IE=edge" />', $songPage);
	$songPage = str_replace(array('<strong>', '</strong>'), '', $songPage);

	$page = simplexml_load_string($songPage);

	$songs = [];
	
	foreach ($page->body->section->section->section->article as $art)
	{
		$newSong = [
			'lang' 	=> substr($art->attributes()['class'], -2),
			'idSong'	=> substr($art->a->attributes()['name'], 4),
			'page'	=> (string) $art->span,
			'title'	=> (string) $art->header->h2,
			'subtitle'	=> (string) $art->header->h3,
			'text'	=> trim((string) $art->div)
		];

		var_dump($newSong);

		$db->query("INSERT INTO song (id_song, title, subtitle, page, text, id_lang) VALUES (
			'{$newSong['idSong']}',
			'{$newSong['title']}',
			'{$newSong['subtitle']}',
			'{$newSong['page']}',
			'" . $db->escape_string($newSong['text']) . "',
			'" . $languages[$newSong['lang']] . "'
		)");

		echo "<pre style='color: red; padding: 3px; background: #efefef'>" . $db->error . "</pre>\n";

		echo "Song #{$newSong['idSong']} inserted, lang {$newSong['lang']}<br>";

		$db->query("INSERT INTO word_song (word, id_song) VALUES ('$word', '{$newSong['idSong']}')");
		echo "Word $word associated to song #{$newSong['idSong']}<br>";
	}
}