<?php
require "environment.php";

global $config;
$config = array();

if(ENVIRONMENT == "development") {
    define("BASE_URL", "http://localhost/php/webservices/devstagram/");
    $config['dbname'] = 'devstagram';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'root';
    $config['dbpass'] = '';
    $config['jwt_secret_key'] ='abC123!';
    

} else {
    define("BASE_URL", "http://localhost\php\webservices\devstagram");
    $config['dbname'] = 'devstagram';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'root';
    $config['dbpass'] = '';
    $config['jwt_secret_key'] ='abC123!';
}