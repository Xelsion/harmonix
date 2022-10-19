<?php

namespace controller\admin;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;
use system\Core;

class RoutesController Extends AController {

    /**
     * @inheritDoc
     */
    public function init( Router $router ): void {
        $routes = $this->getRoutes();
        foreach( $routes as $url => $route ) {
            $router->addRoute($url, $route["controller"], $route["method"] );
        }

        // Add MenuItems to the Menu
        $this::$_menu->insertMenuItem(500, null, "Routen", "/routes");
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/routes" => array("controller" => __CLASS__, "method" => "index"),
        );
    }

    /**
     * @inheritDoc
     */
    public function index(): AResponse {
        $response = new ResponseHTML();
        $all_routes = array();
        Core::$_router->getAllRoutes( PATH_CONTROLLER_ROOT, $all_routes);

        $cache = Core::$_response_cache;
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."routes/index.html");

        if( $cache->isUpToDate() ) {
            print_debug("from cache");
            $view_content = $cache->getContent();
        } else {
            print_debug("from template");
            $view = new Template(PATH_VIEWS."routes/index.html");
            $view->set("routes_list", $all_routes);
            $view_content = $view->parse();
            $cache->saveContent($view_content);
        }

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
        $template->set("view", $view_content);

        $response->setOutput($template->parse());
        return $response;
    }
}
