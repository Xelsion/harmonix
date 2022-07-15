<?php

namespace controller\www;

use JsonException;
use models\Actor;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;

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
        $this::$_menu->insertMenuItem(210, 200, "Actors", "/actors");
        $this::$_menu->insertMenuItem(220, 200, "Charts", "/tests/charts");
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/tests" => array("controller" => __CLASS__, "method" => "index"),
            "/actors" => array("controller" => __CLASS__, "method" => "actors"),
            "/actors/{id}" => array("controller" => __CLASS__, "method" => "actorsDetail"),
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

    /**
     * @throws SystemException
     */
    public function charts(): AResponse {
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", new Template(PATH_VIEWS."tests/charts.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @throws SystemException
     * @throws JsonException
     */
    public function actors() : AResponse {
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("actor_list", Actor::find());
        $template->set("view", new Template(PATH_VIEWS."tests/actors.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @throws SystemException
     * @throws JsonException
     */
    public function actorsDetail( int $id ) : AResponse {
        $results = Actor::find([["id", "=", $id]]);
        if( count($results) === 0 ) {
            redirect("/error/404");
        }
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("actor", $results[0]);
        $template->set("view", new Template(PATH_VIEWS."tests/actors_detail.html"));
        $response->setOutput($template->parse());
        return $response;
    }

}