<?php

require '../vendor/autoload.php';

use \Symfony\Component\HttpFoundation\Request;
use \Silex\Application;

$app = new Application;
$app['config'] = require __DIR__ . '/../config.php';

$app->register(new \Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => $app['config']['templates_dir']
));

$app->register(new \Silex\Provider\LocaleServiceProvider());
$app->register(new \Silex\Provider\TranslationServiceProvider());

$app['translator.domains'] = ['messages' => [
	'es' => [

		//base.twig
		'Lexicon <strong>He Rose</strong>'
		=> 'Léxico <strong>Resucitó</strong>',

		//home.twig
		'Lexicon <small>of the</small> <strong>HE ROSE FROM DEATH</strong>'
		=> 'Léxico <small>del</small> <strong>RESUCITÓ</strong>',
		
		'<strong>Notice</strong>: this lexicon is made after an automatic analysis of all the words mentioned in the songbook. Therefore, many words with similar or identical meaning may appear separately, as well as verbal conjugations. Also, composed terms (e. g. <em>word of God</em>) are not indexed.'
		=> '<strong>Nota</strong>: este léxico es producto de un análisis automático de todas las palabras que aparecen en el libro de cantos. Los verbos se listan en infinitivo, y los nombres y adjetivos, por lo general, en masculino singular, aunque en los cantos las palabras se muestran tal y como aparecen. Además, no se han indexado términos compuestos (p. ej. <em>palabra de Dios</em>).',

		'The songs are taken from the songbook edited in <strong>London 2013</strong>.'
		=> 'Los cantos están tomados del Resucitó editado en <strong>Madrid 2014</strong>.',

		'Explore words <small>(%total%)</small>'
		=> 'Explorar palabras <small>(%total%)</small>',

		'Display list of words'
		=> 'Ver lista de palabras',

		'Display as summary with songs'
		=> 'Ver resumen con cantos',

		'Display as word cloud'
		=> 'Ver como nube de palabras',

		//list.twig
		'All words'
		=> 'Todas las palabras',

		//word.twig
		'Songs where the word <strong>%word%</strong> appears:'
		=> 'Cantos en los que aparece la palabra <strong>%word%</strong>:',

		'appears <strong>%total_occurences%</strong> times in <strong>%total_songs%</strong> song(s)'
		=> 'Aparece <strong>%total_occurences%</strong> veces en <strong>%total_songs%</strong> canto(s)',

		//summary.twig
		'Summary'
		=> 'Vista resumida',

		//cloud.twig
		'The more times each word occurs in the songs, the bigger it is displayed. Colors are just aesthetic.'
		=> 'Las palabras se muestran más grandes cuantas más veces aparecen en los cantos. Los colores de las palabras son puramente decorativos: no significan nada.',
	]
]];

$app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
	'db.options' => array(
		'driver'	=> 'pdo_mysql',
		'host'		=> $app['config']['db']['host'],
		'user'		=> $app['config']['db']['user'],
		'password'	=> $app['config']['db']['password'],
		'dbname'	=> $app['config']['db']['database'],
		'charset'	=> $app['config']['db']['charset']
	),
));

$app['debug'] = $app['config']['debug'];

/** @todo Ya que siempre ocurre, hacer un filtro de esos before siempre, pa no tener que declarar para cada route */
$app->before(function (Request $req, Application $app) {
	$locale = $app['db']->fetchColumn('SELECT locale FROM lang WHERE short = ?', [$app['locale']]);
	setLocale(LC_COLLATE, $locale . '.utf8');
	setLocale(LC_CTYPE, $locale . '.utf8');

	$app['id_lang'] = $app['config']['lang'][$app['locale']];
});

$app->get('/{_locale}', function() use ($app) 
{
	$total = $app['db']->fetchColumn('SELECT COUNT(DISTINCT lemma) FROM lemma WHERE id_lang = ?', [$app['id_lang']]);
	return $app['twig']->render('home.twig', ['total' => $total]);
})->bind('home');

$app->get('/{_locale}/list', "Resucilex\\Controllers\\ViewList::getList")
	->bind('list');

$app->get('/{_locale}/summary', "Resucilex\\Controllers\\ViewSummary::get")
	->bind('summary');

$app->get('/{_locale}/tagcloud', "Resucilex\\Controllers\\ViewList::getCloud")
	->bind('tagcloud');

$app->get('/{_locale}/{word}', "Resucilex\\Controllers\\ViewWord::get")
	->bind('word');

$app->run();