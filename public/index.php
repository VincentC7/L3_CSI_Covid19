<?php

use L3_CSI_Covid19\DB\Eloquant;
use L3_CSI_Covid19\Model\Patient;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require '../vendor/autoload.php';

//Eloquant::start('../conf/conf.ini');

$pdo =  pg_connect('host=localhost port=5432 dbname=covid19 user=postgres password=27071999');

$app = new App();

$app->get('/', function (Request $request, Response $response){

});

try {
    $app->run();
} catch (Throwable $e) {
}