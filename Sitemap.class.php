<?php

class Sitemap {

    //const MAX_SITEMAP_SIZE = 50*1024*1024; // 50Mb
    const MAX_SITEMAP_SIZE = 1024; // bytes

    const MAX_URL_COUNT = 50000;
    //const MAX_URL_COUNT = 10;

    const FILENAME_MASK = 'sitemap_';


    public static $filesCount = 0;


    protected $_file;

    protected $_filename;

    protected $_urlsCount = 0;

    protected $_filesize = 0;


    protected $_gzipLevel = 9;


    protected $_changefreq;

    protected $_priority;

    public function __construct($chahgefreq, $priority) {
        $this->_changefreq = $chahgefreq;
        $this->_priority = $priority;

        $this->start();
    }

    public function __destruct() {
        $this->close();
    }

    protected function start() {

        if (!file_exists(__DIR__ . '/tmp/')) {
            $oldmask = umask(0);
            mkdir(__DIR__ . '/tmp/', 0777);
            umask($oldmask);
        }

        $this->_filename =  self::FILENAME_MASK . ++self::$filesCount . '.xml';

        $this->_file = fopen(__DIR__ . '/tmp/' . $this->_filename, 'w');

        $this->addData('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
        $this->addData('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
    }

    protected function close() {
        $this->addData('</urlset>');
        fclose($this->_file);

        $this->_filesize = 0;
        $this->_urlsCount = 0;

        if (!file_exists(__DIR__ . '/sitemap/')) {
            $oldmask = umask(0);
            mkdir(__DIR__ . '/sitemap/', 0777);
            umask($oldmask);
        }

        $fileGZ = $this->_filename . '.gz';

        file_put_contents(__DIR__ . '/sitemap/' .$fileGZ, gzencode(file_get_contents(__DIR__ . '/tmp/' . $this->_filename), $this->_gzipLevel, FORCE_GZIP));
    }

    protected function wrap($data, $tag) {
        return "<$tag>$data</$tag>";
    }

    protected function addData($data) {
        $this->_filesize += fwrite($this->_file, $data);
    }

    protected function isAllowedAddingUrls() {
        return self::MAX_URL_COUNT > $this->_urlsCount;
    }

    protected function isAllowedAddingString($str) {
        $total = $this->_filesize + strlen($str) + strlen('</urlset>');

        if ($total > self::MAX_SITEMAP_SIZE) {
            return false;
        }

        return true;
    }

    public function addUrl($row) {
        if (!$this->isAllowedAddingUrls()) {
            $this->close();
            $this->start();
        }

        $url = $this->wrap(htmlspecialchars($row['loc']), 'loc');
        $url .= $this->wrap(date(DATE_RFC3339, strtotime($row['lastmod'])), 'lastmod');
        $url .= $this->wrap($this->_changefreq, 'changefreq');
        $url .= $this->wrap($this->_priority, 'priority');

        $url = $this->wrap($url, 'url');

        if (!$this->isAllowedAddingString($url)) {
            $this->close();
            $this->start();
        }

        $this->addData($url);

        $this->_urlsCount++;
    }
}