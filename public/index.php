<?php

use L3_CSI_Covid19\Controller\DepartementController;
use L3_CSI_Covid19\Controller\HomeController;
use L3_CSI_Covid19\Controller\HopitalController;
use L3_CSI_Covid19\Controller\HospitalisationController;
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

//pages de gestion des patients
$app->get('/Patient', PatientController::class.":index")->setName("patient");
$app->post('/Patient', PatientController::class.":new");
$app->get('/Patient/{numsecu}', PatientController::class.":view")->setName('voirPatient');
$app->post('/Patient/{numsecu}', PatientController::class.":update");


$app->get('/Patient/{numsecu}/Hospitaliser', HospitalisationController::class.":index")->setName('hospitaliserPatient');
$app->post('/Patient/{numsecu}/Hospitaliser/{nohopital}', HospitalisationController::class.":new")->setName('dohospitaliserPatient');
$app->post('/Patient/{numsecu}/Hospitalisation/{noHosp}', HospitalisationController::class.":update")->setName('fin_hospitalisation');
$app->get('/Patient/{numsecu}/Transferer/{nohopital}', HospitalisationController::class.":demandetransfer")->setName('transferpatient');
$app->post('/Patient/{numsecu}/Transferer/{newHopital}', HospitalisationController::class.":transferer")->setName('dotransfererPatient');

//pages de gestion des hopitaux
$app->get('/Hopital', HopitalController::class.":index")->setName("hopitaux");
$app->post('/Hopitaux/{nohopital}', HopitalController::class.":update")->setName("modifier_hopital");
$app->get('/Hopital/{nohopital}', HopitalController::class.":view")->setName("voirHopital");

//pages de gestion d'un département
$app->get('/Departements', DepartementController::class.":index")->setName("departements");
$app->post('/Departements/{departement}', DepartementController::class.":update")->setName("modifier_departement");
$app->get('/Departements/{nodep}', DepartementController::class.":view")->setName("list_confines");


$app->run();
