<?php
namespace Core;

use PDO;

class Model {
    protected $pdo;

    public function __construct(){
        global $config;

        try {
            
            $this->pdo = new PDO('mysql:dbname='.$config['dbname'].';charset=utf8;host='.$config['host'], $config['dbuser'], $config['dbpass']);

        } catch (\PDOException $e) {
            echo "ERRO: ".$e->getMessage();
        }
    }
}