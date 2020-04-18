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
     * @param null $args
     */
    public function render(ResponseInterface $response, $file, $args){
        $this->container->view->render($response,$file, $args);
    }

}