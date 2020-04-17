<?php

use L3_CSI_Covid19\DB\Eloquant;
use L3_CSI_Covid19\Model\Patient;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require '../vendor/autoload.php';

Eloquant::start('../conf/conf.ini');


$app = new App();

$app->get('/', function (Request $request, Response $response){
    $liste = Patient::Select("nom")->get();
    foreach ($liste as $p){
        echo $p['nom']." ";
    }
});

try {
    $app->run();
} catch (Throwable $e) {
}