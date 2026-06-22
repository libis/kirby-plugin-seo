<?php
Kirby::plugin('libis/seo', [
	'pageMethods' => [		
		'seoTitle' => function () {
			$site = site();

			$pageTitle = $this->seo_title()->value() != "" ? $this->seo_title()->value() : $this->title()->value();

			$siteTitle = $site->site_meta_title()->value();

			$titleFormat = $site->title_pattern_default();

			$title = $siteTitle;	

			switch ($titleFormat) {
				case "title_stripe_site":
					$title = trim($pageTitle . ' | ' . $siteTitle);
					break;
				case "site_stripe_title":
					$title = trim($siteTitle . ' | ' . $pageTitle);
					break;
				case "title_comma_site":
					$title = trim($pageTitle . ', ' . $siteTitle);
					break;
				case "site_comma_title":
					$title = trim($siteTitle . ', ' . $pageTitle);
					break;
				case "title":
					$title = $pageTitle;
					break;
				default:
					$title = $siteTitle;	
			}

			return $title;
		},
		'seoRobots' => function () {
      // Page-level override -- priority
      if ($this->seo_robots()->isNotEmpty() && $this->seo_robots()->value() !== 'inherit') {
        return str_replace('_', ', ', $this->seo_robots()->value());
      }

      // Template-based noindex from config
      $noindexTemplates = option('libis.seo.noindexTemplates', []);

      if (in_array($this->intendedTemplate()->name(), $noindexTemplates, true)) {
        return 'noindex, follow';
      }

      // Fallback to site-wide default
      return str_replace('_', ', ', site()->default_robots()->value());
    },
		'structuredData' => function () {
			if (str_contains($this->seoRobots(), 'noindex')) {
        return null;
      }
			return [
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'name'     => $this->seoTitle(),
        'url'      => $this->url(),
        'dateModified' => $this->modified('d/MM/YYYY HH:mm'),
      ];
		},
		'seoPermalink' => function () {	
			if (!$this->exists()) {
				return site()->url() . '/page/permalink/-';
			}

    	return site()->url() . '/page/permalink/' .
        str_replace('page://', '', $this->uuid());
    }
	],
	'blueprints' => [
		'seo-page' => __DIR__ . '/blueprints/tabs/seo-page.yml',
		'seo-site' => __DIR__ . '/blueprints/tabs/seo-site.yml',
	],
	'snippets' => [
		'sitemap' => __DIR__ . '/snippets/sitemap.php',
		'seo' => __DIR__ . '/snippets/seo.php',
		'seo-jsonld' => __DIR__ . '/snippets/seo-jsonld.php',
	],
	'routes' => [
    [
      'pattern' => 'sitemap.xml',
      'action'  => function() {
          $pages = site()->pages()->index()->filter(function ($page) {

						if (!$page->isListed() && !$page->isHomePage()) {
							return false;
						}

						if ($page->seoRobots() === 'noindex, nofollow' ||
								$page->seoRobots() === 'noindex, follow') {
							return false;
						}

						if ($page->exclude_from_sitemap()->toBool()) {
							return false;
						}

						return true;
					});


          $content = snippet('sitemap', ['pages' => $pages], true);

          // return response with correct header type
          return new Kirby\Cms\Response($content, 'application/xml');
      }
    ],
    [
      'pattern' => 'sitemap',
      'action'  => function() {
        return go('sitemap.xml', 301);
      }
    ],
		[
			'pattern' => 'page/permalink/(:any)',
      'language' => '*',
      'action'  => function ($language, $permaid) {
				$page = site()->find('page://' . $permaid);
				if($page) {
					go($page->url());
				}
				else {
					return 'failed';
				}
			}
		]
  ],
	'translations' => (function () {
		$translations = [];
		$dir = __DIR__ . '/translations';

		foreach (glob($dir . '/*.json') as $file) {
		$lang = basename($file, '.json');
		$json = file_get_contents($file);
		$translations[$lang] = json_decode($json, true);
		}

		return $translations;
	})(),
]);
