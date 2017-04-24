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
$app['translator.domains'] = ['messages' => require __DIR__ . '/../translations.php'];

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


$app->before(function (Request $req, Application $app) {
	$locale = $app['db']->fetchColumn('SELECT locale FROM lang WHERE short = ?', [$app['locale']]);
	setLocale(LC_COLLATE, $locale . '.utf8');
	setLocale(LC_CTYPE,   $locale . '.utf8');

	$app['id_lang'] = $app['config']['lang'][$app['locale']];
});


$app->get('/', function(Request $req) use ($app)
{
	$acceptLang = $req->getPreferredLanguage(
		array_column($app['config']['lang'], 'short')
	);
	
	return $app->redirect($app['url_generator']->generate(
		'home',
		['_locale'=>$acceptLang]
	));
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