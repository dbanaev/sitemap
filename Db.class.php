<?php

class Db {

    private static $_instance;

    private $_dbh;

    private function __construct() {
        $this->_dbh = new \PDO('mysql:host=127.0.0.1;dbname=sitemap;charset=utf8', 'root', 'ressiver');
    }

    private function __clone() {}

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function query($sql, $params=[]) {
        $sth = $this->_dbh->prepare($sql);
        $sth->execute($params);

        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }

}