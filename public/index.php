<?php

use L3_CSI_Covid19\Controller\DepartementController;
use L3_CSI_Covid19\Controller\HomeController;
use L3_CSI_Covid19\Controller\HopitalController;
use L3_CSI_Covid19\Controller\PatientController;
use L3_CSI_Covid19\Controller\StatistiquesController;
use L3_CSI_Covid19\DB\Eloquant;
use Slim\App;

require '../vendor/autoload.php';

//Démarage de la base de données
Eloquant::start('../conf/conf.ini');

$app = new App();

require('../src/container.php');

//page de d'acceuil
$app->get('/', HomeController::class.":home");

//page de gestion des patients
$app->get('/Patient', PatientController::class.":home");

//page de gestion des hopitaux
$app->get('/Hopitaux', HopitalController::class.":home");

//page de gestion d'un département
$app->get('/Departements', DepartementController::class.":home");

//page de des statisiques
$app->get('/Stat', StatistiquesController::class.":home");


$app->run();