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

#[Route("routes")]
class RoutesController Extends AController {

    /**
     * Get a list of all Routes
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("/")]
    public function index(): AResponse {
        $response = new HtmlResponse();
        $routes = System::$Core->router->getSortedRoutes();

        $cache = System::$Core->response_cache;
        $cache->initCacheFor(__METHOD__);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."template.html");
        $cache->addFileCheck(PATH_VIEWS."routes/index.html");

        if( self::$caching && $cache->isUpToDate() ) {
            $view_content = $cache->getContent();
        } else {
            $view = new Template(PATH_VIEWS."routes/index.html");
            $view->set("routes_list", $routes);
            $view_content = $view->parse();
            if( self::$caching ) {
                $cache->saveContent($view_content);
            }
        }

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", $view_content);

        $response->setOutput($template->parse());
        return $response;
    }
}
