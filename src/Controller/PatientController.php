<?php


namespace L3_CSI_Covid19\Controller;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PatientController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $this->render($response,'pages/patient.twig');
    }

    public function nouveauPatient(RequestInterface $request, ResponseInterface $response){

    }
}