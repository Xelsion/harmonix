<?php

namespace controller\admin;

use system\Core;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;

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
        $response = new HtmlResponse();
        $all_routes = array();
        Core::$_router->getAllRoutes( PATH_CONTROLLER_ROOT, $all_routes);

        $cache = Core::$_response_cache;
        $cache->initCacheFor(__METHOD__);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."routes/index.html");

        if( $cache->isUpToDate() ) {
            $view_content = $cache->getContent();
        } else {
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
