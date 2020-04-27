<?php

namespace L3_CSI_Covid19\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HomeController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $bilan = new Bilan($this->get_PDO());
        $this->render($response,'pages/home.twig', ['bilan'=>$bilan->get_statisiques(),'bilan_journalier'=>$bilan->get_bilan_epidemie('DD-MON-YYYY')]);
    }
}