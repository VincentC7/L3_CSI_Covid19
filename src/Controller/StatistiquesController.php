<?php


namespace L3_CSI_Covid19\Controller;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class StatistiquesController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $this->render($response,'pages/stats.twig',[]);
    }

    public function global_stat(RequestInterface $request, ResponseInterface $response, $cutTime){
        /*
          cutTime sert a découpé les échelles de temps
        */


    }
}
