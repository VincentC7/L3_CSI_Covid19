<?php


namespace L3_CSI_Covid19\Controller;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use L3_CSI_Covid19\Model\Patient;

class StatistiquesController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $this->render($response,'pages/stats.twig');
    }

    public function global_stat(RequestInterface $request, ResponseInterface $response){
        $dataStat = new Array();

        //tranche de 10
        for($i = 0; $i < 10; $i++){
            $dataStat[$i][0] = "M";
            $dataStat[$i][1] = "F";
            $dataStat[$i][0][0] = Patient
        }
    }
}
