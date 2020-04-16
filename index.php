<?php

use L3_CSI_Covid19\DB\Eloquant;
use Slim\Slim;

require 'vendor/autoload.php';

Eloquant::start('conf/conf.ini');

$slim = new Slim();

$slim->get('/', function (){
    echo "test";
})->name('page_acceuil');