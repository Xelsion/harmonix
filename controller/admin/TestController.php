<?php

namespace controller\admin;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;
use system\System;

#[Route("tests")]
class TestController Extends AController {

    /**
     * Shows the starting page of the test controller
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("/")]
    public function index(): AResponse {
        $response = new HtmlResponse();
        $all_routes = array();
        System::$Core->router->getAllRoutes( PATH_CONTROLLER_ROOT, $all_routes);

        $cache = System::$Core->response_cache;
        $cache->initCacheFor(__METHOD__);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."test/index.html");

        if( $cache->isUpToDate() ) {
            $view_content = $cache->getContent();
        } else {
            $view = new Template(PATH_VIEWS."test/index.html");
            $view->set("routes_list", $all_routes);
            $view_content = $view->parse();
            $cache->saveContent($view_content);
        }

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", $view_content);

        $response->setOutput($template->parse());
        return $response;
    }
}