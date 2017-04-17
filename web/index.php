<?php

require '../vendor/autoload.php';

use \Symfony\Component\HttpFoundation\Request;
use \Silex\Application;

$config = require __DIR__ . '/../config.php';

$app = new Application;

$app->register(new \Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => $config['templates_dir']
));

$app->register(new \Silex\Provider\LocaleServiceProvider());
$app->register(new \Silex\Provider\TranslationServiceProvider());

$app['translator.domains'] = ['messages' => [
	'es' => [
		'Lexicon <small>of the</small> <strong>HE ROSE FROM DEATH</strong>'
		=> 'Léxico <small>del</small> <strong>RESUCITÓ</strong>',
		
		'<strong>Notice</strong>: this lexicon is made after an automatic analysis of all the words mentioned in the songbook. Therefore, many words with similar or identical meaning may appear separately, as well as verbal conjugations. Also, composed terms (e. g. <em>word of God</em>) are not indexed.'
		=> '<strong>Nota</strong>: este léxico es producto de un análisis automático de todas las palabras que aparecen en el libro de cantos. Por tanto, muchas palabras con el mismo significado o conjugaciones verbales aparecerán como palabras diferentes. Además, no se han indexado términos compuestos (p. ej. <em>palabra de Dios</em>).',

		'The songs are taken from the songbook edited in <strong>London 2013</strong>.'
		=> 'Los cantos están tomados del Resucitó editado en <strong>Madrid 2014</strong>.',

		'All words (%total%)'
		=> 'Todas las palabras (%total%)',

		'Display list of words'
		=> 'Ver lista de palabras',

		'Display as summary with songs'
		=> 'Ver resumen con cantos',

		'Display as word cloud'
		=> 'Ver como nube de palabras',

		'All words'
		=> 'Todas las palabras',

		'Songs where the word <strong>%word%</strong> appears:'
		=> 'Cantos en los que aparece la palabra <strong>%word%</strong>:',

		'appears <strong>%total_occurences%</strong> times in <strong>%total_songs%</strong> song(s)'
		=> 'Aparece <strong>%total_occurences%</strong> veces en <strong>%total_songs%</strong> canto(s)',

		'Summary'
		=> 'Vista resumida',

		'The more times each word occurs in the songs, the bigger it is displayed. Colors are just aesthetic.'
		=> 'Las palabras se muestran más grandes cuantas más veces aparecen en los cantos. Los colores de las palabras son puramente decorativos: no significan nada.',
	]
]];

$app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
	'db.options' => array(
		'driver'	=> 'pdo_mysql',
		'host'		=> $config['db']['host'],
		'user'		=> $config['db']['user'],
		'password'	=> $config['db']['password'],
		'dbname'	=> $config['db']['database'],
		'charset'	=> $config['db']['charset']
	),
));

//$app['debug'] = true;

/** @todo Ya que siempre ocurre, hacer un filtro de esos before siempre, pa no tener que declarar para cada route */
$setLocale = function (Request $req, Application $app) {
	$locale = $app['db']->fetchColumn('SELECT locale FROM lang WHERE short = ?', [$app['locale']]);
	setLocale(LC_COLLATE, $locale . '.utf8');
};
	

$app->get('/{_locale}', function() use ($app) 
{
	$total = $app['db']->fetchColumn('SELECT COUNT(DISTINCT word) FROM word_song JOIN song USING (id_song) JOIN lang USING (id_lang) WHERE lang.short = ?', [$app['locale']]);
	return $app['twig']->render('home.twig', ['total' => $total]);
})
	->bind('home')
	->before($setLocale);

$app->get('/{_locale}/list', "Resucilex\\Controllers\\ViewList::getList")
	->bind('list')
	->before($setLocale);

$app->get('/{_locale}/summary', "Resucilex\\Controllers\\ViewSummary::get")
	->bind('summary')
	->before($setLocale);

$app->get('/{_locale}/tagcloud', "Resucilex\\Controllers\\ViewList::getCloud")
	->bind('tagcloud')
	->before($setLocale);

$app->get('/{_locale}/{word}', "Resucilex\\Controllers\\ViewWord::get")
	->bind('word')
	->before($setLocale);

$app->run();