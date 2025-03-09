<?php

return [

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

	'debug' => true,

	// FEATURE FLAGS
	'feature_show_pos' 		=> true,
	'feature_show_related' 	=> false,
];