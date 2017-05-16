<?php

require '../vendor/autoload.php';

use \Silex\Application;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

$app['mbUcFirst'] = $app->protect(function ($string) {
	return mb_strtoupper(mb_substr($string, 0, 1))
	. mb_strtolower(mb_substr($string, 1, mb_strlen($string)));
});

$app->before(function (Request $req, Application $app) {
	
	$locale = $app['db']->fetchColumn(
		'SELECT locale FROM lang WHERE short = ?', 
		[$app['locale']]
	);
	
	setLocale(LC_COLLATE, $locale . '.utf8');
	setLocale(LC_CTYPE,   $locale . '.utf8');

	$app['id_lang'] = $app['config']['lang'][$app['locale']];

	$app['absoluteUriWithoutQuery'] = $req->getScheme()
		. '://'
		. $req->getHttpHost()
		. strtok($req->getRequestUri(), '?');

	$app['absoluteBasePath'] = $req->getScheme()
		. '://'
		. $req->getHttpHost()
		. $req->getBasePath();

	//Generate URLs of the currently requested page in all locales
	if (false !== array_search('_locale', array_keys($req->attributes->get('_route_params'))))
	{
		$currentRouteWithAllLocales = [];

		$locales = array_column($app['config']['lang'], 'short');
		foreach ($locales as $locale)
		{
			$route_params = $req->attributes->get('_route_params');
			$route_params['_locale'] = $locale;

			$currentRouteWithAllLocales[$locale] = $app['url_generator']->generate(
				$req->attributes->get('_route'),
				$route_params,
				UrlGeneratorInterface::ABSOLUTE_URL
			);
		}

		$app['currentRouteWithAllLocales'] = $currentRouteWithAllLocales;
	}
});

/**
 * Disk-based, full-output cache system. 
 * It does not work without proper Apache config (see .htaccess)
 */
$app->after(function(Request $request, Response $response)
{
	if (200 != $response->getStatusCode())
	{
		return;
	}

	$reqUri = urldecode($request->getPathInfo());
	$cacheFile = __DIR__ . '/cache' . $reqUri . '.html';
	
	if (!file_exists(dirname($cacheFile)))
	{
		mkdir(dirname($cacheFile), 0777, true);
	}

	if (!file_exists($cacheFile))
	{
		file_put_contents(
			$cacheFile, 
			$response->getContent() . "<!-- cached " . date('r') . " -->"
		);
	}
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

$validLocales = implode('|', array_column($app['config']['lang'], 'short'));

$app->get('/{_locale}', function() use ($app) 
{
	$total = $app['db']->fetchColumn(
		'SELECT COUNT(DISTINCT lemma) FROM lemma WHERE id_lang = ?', 
		[$app['id_lang']]
	);

	return $app['twig']->render('home.twig', [
		'total' 	 => $total,
		'bodyClass'  => 'home',
		'hrefLangs'  => $app['currentRouteWithAllLocales'],
	]);

})
	->assert('_locale', $validLocales)
	->bind('home');


$app->get('/{_locale}/list/{dufour}', "Resucilex\\Controllers\\ViewList::getList")
	->value('dufour', false)
	->assert('_locale', $validLocales)
	->bind('list');

$app->get('/{_locale}/summary', "Resucilex\\Controllers\\ViewSummary::get")
	->assert('_locale', $validLocales)
	->bind('summary');

$app->get('/{_locale}/tagcloud', "Resucilex\\Controllers\\ViewList::getCloud")
	->assert('_locale', $validLocales)
	->bind('tagcloud');

$app->get('/{_locale}/{word}', "Resucilex\\Controllers\\ViewWord::get")
	->assert('_locale', $validLocales)
	->bind('word');

$app->get('/sitemap.xml', "Resucilex\\Controllers\\Sitemap::get");

$app->run();