<?php

namespace controller\www;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ErrorController extends AController {

    private array $_error_codes = array(403, 404);

    /**
     * @inheritDoc
     */
    public function init( Router $router ): void {
        // Add routes to router
        $routes = $this->getRoutes();
        foreach( $routes as $url => $route ) {
            $router->addRoute($url, $route["controller"], $route["method"] );
        }
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/error" => array("controller" => __CLASS__, "method" => "index"),
            "/error/{error_code}" => array("controller" => __CLASS__, "method" => "error")
        );
    }

    /**
     * @inheritDoc
     */
    public function index(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", new Template(PATH_VIEWS."home/index.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @throws SystemException
     */
    public function error( int $error_code ): AResponse {
        $response = new HtmlResponse();
        $response->status_code = $error_code;
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", new Template(PATH_VIEWS."error/".$error_code.".html"));
        $response->setOutput($template->parse());
        return $response;
    }
}
