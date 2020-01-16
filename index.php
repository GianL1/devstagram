<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");

require "config.php";
require "vendor/autoload.php";
require "routers.php";


$core = new Core\Core();
$core->run();