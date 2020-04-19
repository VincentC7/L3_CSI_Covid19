<?php

use L3_CSI_Covid19\Controller\DepartementController;
use L3_CSI_Covid19\Controller\HomeController;
use L3_CSI_Covid19\Controller\HopitalController;
use L3_CSI_Covid19\Controller\PatientController;
use L3_CSI_Covid19\Controller\StatistiquesController;
use L3_CSI_Covid19\DB\Eloquant;
use Slim\App;

require 'vendor/autoload.php';

//Démarage de la base de données
Eloquant::start('conf/conf.ini');

$app = new App();

require('src/container.php');

//page de d'acceuil
$app->get('/', HomeController::class.":home")->setName("home");

//page de gestion des patients
$app->get('/Patient', PatientController::class.":home")->setName("patient");
$app->post('/Patient', PatientController::class.":nouveauPatient");
$app->get('/Patient/{numsecu}', PatientController::class.":modifierPatient")->setName('modifierPatient');
$app->post('/Patient/{numsecu}', PatientController::class.":modifierPatient");

//page de gestion des hopitaux
$app->get('/Hopitaux', HopitalController::class.":home")->setName("hopitaux");
$app->post('/Hopitaux', HopitalController::class.":update")->setName("updatehopitaux");

//page de gestion d'un département
$app->get('/Departements', DepartementController::class.":home")->setName("departements");

//page de des statisiques
$app->get('/Stat', StatistiquesController::class.":home")->setName("stats");


$app->run();