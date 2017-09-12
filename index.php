<?php

require_once __DIR__ . '/Sitemap.class.php';
require_once __DIR__ . '/Db.class.php';

set_time_limit(0);
ini_set('memory_limit', '128M');


exec('rm -rf sitemap/*');
exec('rm -rf tmp/*');


$baseUrl = 'http://example.com/';

$db = Db::getInstance();

// ads
$sql = 'SELECT id, updated_at FROM ad WHERE active = 1 LIMIT 20';
$ads = $db->query($sql);

if ($ads) {
    $sitemap = new Sitemap('weekly', '0.9');

    foreach ($ads as $ad) {

        $sitemap->addUrl([
            'loc' => $baseUrl . 'price/' . $ad->id . '.html',
            'lastmod' => $ad->updated_at
        ]);
    }
}


// articles
$sql = 'SELECT id, updated_at FROM article WHERE published = 1 LIMIT 10';
$articles = $db->query($sql);

if ($articles) {
    $sitemap = new Sitemap('daily', '0.8');
    foreach ($articles as $article) {

        $sitemap->addUrl([
            'loc' => $baseUrl . 'pub/' . $article->id . '.html',
            'lastmod' => $article->updated_at
        ]);
    }
}

// companies
$sql = 'SELECT id, updated_at FROM company LIMIT 10';
$companies = $db->query($sql);

if ($companies) {
    $sitemap = new Sitemap('weekly', '0.8');
    foreach ($companies as $company) {

        $sitemap->addUrl([
            'loc' => $baseUrl . 'company/' . $company->id . '.html',
            'lastmod' => $company->updated_at
        ]);
    }
}

// countries
$sql = 'SELECT id, alias, updated_at FROM country LIMIT 10';
$countries = $db->query($sql);

if ($countries) {
    $sitemap = new Sitemap('weekly', '0.8');
    foreach ($countries as $country) {

        $sitemap->addUrl([
            'loc' => $baseUrl . 'country/' . $country->alias . '/',
            'lastmod' => $country->updated_at
        ]);
    }
}


// news
$sql = 'SELECT id, updated_at FROM news WHERE published = 1 LIMIT 20';
$news = $db->query($sql);

if ($news) {
    $sitemap = new Sitemap('daily', '0.8');
    foreach ($news as $item) {

        $sitemap->addUrl([
            'loc' => $baseUrl . 'news/' . $item->id . '.html',
            'lastmod' => $item->updated_at
        ]);
    }
}
// add static page in news section
$sitemap->addUrl([
    'loc' => $baseUrl . 'news-all.html',
    'lastmod' => '2017-09-01'
]);



// static page
$sql = 'SELECT id, url, updated_at FROM page LIMIT 10';
$pages = $db->query($sql);

if ($pages) {
    $sitemap = new Sitemap('weekly', '0.9');
    foreach ($pages as $page) {

        $sitemap->addUrl([
            'loc' => $baseUrl . $page->url,
            'lastmod' => $page->updated_at
        ]);
    }
}


$sitemapIndex = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />');

// natural sort
for ($i = 1; $i <= Sitemap::$filesCount; $i++) {
    $sitemapElem = $sitemapIndex->addChild('sitemap');
    $sitemapElem->addChild('loc', $baseUrl . 'sitemap/sitemap_' . $i . '.xml.gz');
}

file_put_contents(__DIR__ . '/sitemap.xml', $sitemapIndex->asXML());