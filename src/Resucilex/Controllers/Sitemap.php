<?php

namespace Resucilex\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Sitemap
{
	/**
	 * @type  \Silex\Application
	 */
	protected $app;

	/**
	 * Generates the Sitemap.
	 * 
	 * @param  \Silex\Application	$app	The Silex app
	 * @return string						The rendered view
	 */
	public function get(\Silex\Application $app)
	{
		$this->app = $app;

		$urls 			= [];
		$languages 		= array_column($app['config']['lang'], 'short');
		$urlsWithLocale = ['home', 'list', 'summary', 'tagcloud'];

		foreach ($languages as $lang)
		{
			foreach ($urlsWithLocale as $url)
			{
				$urls[] = array(
					'loc' 			=> $app['url_generator']->generate(
						$url, 
						['_locale' => $lang],
						UrlGeneratorInterface::ABSOLUTE_URL
					),
					'priority' 		=> 1,
					'changefreq' 	=> 'weekly',
					'lastmod' 		=> date('c', $this->getLastMod($url, ['_locale' => $lang]))
				);
			}
		}

		$words = $app['db']->fetchAllAssociative('SELECT distinct lemma, id_lang FROM lemma');

		foreach ($words as $word)
		{
			$urlParams = ['_locale' => $app['config']['lang'][$word['id_lang']]['short'], 'word' => $word['lemma']];

			$urls[] = array(
				'loc' => $app['url_generator']->generate(
					'word', 
					$urlParams,
					UrlGeneratorInterface::ABSOLUTE_URL
				),
				'priority' 		=> '0.7',
				'changefreq' 	=> 'weekly',
				'lastmod' 		=> date('c', $this->getLastMod($url, $urlParams))
			);
		}

		return new Response(
			$app['twig']->render('sitemap.twig', array('urls' => $urls)),
			200,
			['Content-Type' => 'application/xml']
		);
	}

	protected function getLastMod($url, $params)
	{
		$cachedPage = './cache' . $this->app['url_generator']->generate($url, $params) . '.html';
		return file_exists($cachedPage) ? filemtime($cachedPage) : time();
	}
}
