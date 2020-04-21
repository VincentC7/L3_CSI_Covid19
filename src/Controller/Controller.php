<?php


namespace L3_CSI_Covid19\Controller;

use PDO;
use Psr\Http\Message\ResponseInterface;


class Controller {
    protected $container;

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
     * @param $args
     */
    public function render(ResponseInterface $response, $file, $args = []){
        $this->container->view->render($response,$file, $args);
    }

    public function redirect(ResponseInterface $response, $nom,$args =[]){
        return $response->withStatus(302)->withHeader('Location', $this->container->router->pathFor($nom, $args));
    }

    public function afficher_message($message, $type = 'valide'){
        if (!isset($_SESSION['message'])){
            $_SESSION['message'] = [];
        }
        $_SESSION['message'][$type] = $message;
    }

    public function get_PDO() : PDO {
        return $this->container->pdo;
    }

}