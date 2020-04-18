<?php


namespace L3_CSI_Covid19\Controller;

use Psr\Http\Message\ResponseInterface;


class Controller {
    private $container;

    /**
     * Controller constructor.
     * @param $container
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * @param ResponseInterface $response
     * @param $file
     */
    public function render(ResponseInterface $response, $file){
        $this->container->view->render($response,$file);
    }

}