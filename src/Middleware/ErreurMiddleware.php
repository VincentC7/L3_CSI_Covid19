<?php

namespace L3_CSI_Covid19\Middleware;



use Slim\Http\Response;
use Slim\Http\Request;
use Twig\Environment;

class ErreurMiddleware {
    private $twig;

    /**
     * ErreurMiddleware constructor.
     * @param $twig
     */
    public function __construct(Environment $twig) {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, $fonction) {
        $this->twig->addGlobal('message',isset($_SESSION['message']) ? $_SESSION['message'] : []);
        if (isset($_SESSION['message'])){
            unset($_SESSION['message']);
        }
        return $fonction($request,$response);
    }
}