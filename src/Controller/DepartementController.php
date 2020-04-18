<?php


namespace L3_CSI_Covid19\Controller;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DepartementController {
    private $container;

    /**
     * DepartementController constructor.
     * @param $container
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $this->container->view->render($response,'pages/departement.twig');
    }
}