<?php

return array(

	'db' => [
		'host'		=> 'localhost',
		'user'		=> 'your MySQL user',
		'password'	=> 'your MySQL passwords',
		'database'	=> 'the MySQL database',
		'charset'	=> 'utf8'
	],

	'templates_dir'	=> __DIR__ . '/templates',

	// Replica of DB table lang for better speed
	/** @todo Do this elegantly: some DB query and then cache */
	'lang' => [
		1 => [
			'id_lang' 	=> '1',
			'name' 		=> 'Español',
			'short' 	=> 'es',
			'locale' 	=> 'es_ES',
		],
		2 => [
			'id_lang' 	=> '2',
			'name' 		=> 'English',
			'short' 	=> 'en',
			'locale' 	=> 'en_GB',
		],
		'es' => 1,
		'en' => 2,
	],

	'debug' 				=> false,

	// FEATURE FLAGS
	'feature_show_pos' 		=> false,
	'feature_show_related' 	=> false,

);