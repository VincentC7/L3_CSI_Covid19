<?php


namespace L3_CSI_Covid19\Controller;


use L3_CSI_Covid19\Model\Patient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PatientController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $patients = Patient::Select("*")->get();
        $this->render($response,'pages/patient.twig',['patients'=> $patients]);
    }

    public function nouveauPatient(RequestInterface $request, ResponseInterface $response){

    }

    public function modifierPatient(RequestInterface $request, ResponseInterface $response){
        echo "à faire";
    }
}