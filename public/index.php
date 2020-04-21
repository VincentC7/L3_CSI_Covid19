<?php

use L3_CSI_Covid19\Controller\DepartementController;
use L3_CSI_Covid19\Controller\HomeController;
use L3_CSI_Covid19\Controller\HopitalController;
use L3_CSI_Covid19\Controller\PatientController;
use L3_CSI_Covid19\Controller\StatistiquesController;
use L3_CSI_Covid19\Middleware\ErreurMiddleware;
use Slim\App;

require __DIR__ . '/../vendor/autoload.php';


session_start();
$app = new App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

require(__DIR__ .'/../src/container.php');


$container = $app->getContainer();
// ==================== middleware ====================
$app->add(new ErreurMiddleware($container->get('view')->getEnvironment()));


// ==================== routes ====================
//page de d'acceuil
$app->get('/', HomeController::class.":home")->setName("home");

//page de gestion des patients
$app->get('/Patient', PatientController::class.":index")->setName("patient");
$app->post('/Patient', PatientController::class.":new");

$app->get('/Patient/{numsecu}', PatientController::class.":afficher_modifier_patient")->setName('modifierPatient');
$app->post('/Patient/{numsecu}', PatientController::class.":update");

$app->get('/Patient/{numsecu}/Hospitaliser', PatientController::class.":hospitaliserPatient")->setName('hospitaliserPatient');
$app->post('/Patient/{numsecu}/Hospitaliser', PatientController::class.":hospitaliserPatient");

//page de gestion des hopitaux
$app->get('/Hopitaux', HopitalController::class.":index")->setName("hopitaux");
$app->post('/Hopitaux/{nohopital}', HopitalController::class.":update")->setName("modifier_hopital");

//page de gestion d'un département
$app->get('/Departements', DepartementController::class.":index")->setName("departements");
$app->post('/Departements/{departement}', DepartementController::class.":update")->setName("modifier_departement");

//page de des statisiques
$app->get('/Stat', StatistiquesController::class.":home")->setName("stats");


$app->run();
