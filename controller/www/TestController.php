<?php

namespace controller\www;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;

class TestController extends AController {

    /**
     * @inheritDoc
     */
    public function init( Router $router ): void {
        // Add routes to router
        $routes = $this->getRoutes();
        foreach( $routes as $url => $route ) {
            $router->addRoute($url, $route["controller"], $route["method"] );
        }

        // Add MenuItems to the Menu
        $this::$_menu->insertMenuItem(200, null, "Tests", "/tests");
        $this::$_menu->insertMenuItem(210, 200, "Charts", "/tests/charts");
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/tests" => array("controller" => __CLASS__, "method" => "index"),
            "/tests/charts" => array("controller" => __CLASS__, "method" => "charts")
        );
    }

    /**
     * @inheritDoc
     */
    public function index(): AResponse {
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", new Template(PATH_VIEWS."tests/index.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    public function charts(): AResponse {
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", new Template(PATH_VIEWS."tests/charts.html"));
        $response->setOutput($template->parse());
        return $response;
    }
}