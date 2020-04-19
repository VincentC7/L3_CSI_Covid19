<?php


namespace L3_CSI_Covid19\Controller;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HopitalController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $this->render($response,'pages/hopital.twig',[]);
    }

    public function update(RequestInterface $request, ResponseInterface $response){
    }
}