<?php

namespace controller\www;

use JsonException;
use models\ActorModel;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;
use system\helper\RequestHelper;
use system\System;

#[Route("tests")]
class TestController extends AController {

    /**
     * Shows the starting page of the test controller
     *
     * @throws SystemException
     */
    #[Route("/", HTTP_GET)]
    public function index(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", new Template(PATH_VIEWS . "tests/index.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("charts", HTTP_GET)]
    public function charts(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", new Template(PATH_VIEWS."tests/charts.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     *
     * @throws SystemException
     * @throws JsonException
     */
    #[Route("actors", HTTP_GET)]
    public function actors() : AResponse {
        $response = new HtmlResponse();
        $params = RequestHelper::getPaginationParams();

        $cache = System::$Core->response_cache;
        $cache->initCacheFor(__METHOD__, ...$params);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."template.html");
        $cache->addFileCheck(PATH_VIEWS."tests/actors.html");
        $cache->addDBCheck("mvc", "actors");
        if( self::$caching && $cache->isUpToDate() ) {
            $view_content = $cache->getContent();
        } else {
            $template = new Template(PATH_VIEWS . "template.html");
            $template->set("actor_list", ActorModel::find());
            $template->set("view", new Template(PATH_VIEWS . "tests/actors.html"));
            $view_content = $template->parse();

            // if caching is enabled write the generated output into the cache file
            if(self::$caching) {
                $cache->saveContent($view_content);
            }
        }
        $response->setOutput($view_content);
        return $response;
    }

    /**
     *
     * @throws SystemException
     * @throws JsonException
     */
    #[Route("actors/{actor_id}", HTTP_GET)]
    public function actorsDetail( int $actor_id ) : AResponse {
        $results = ActorModel::find([["id", "=", $actor_id]]);
        if( count($results) === 0 ) {
            redirect("/error/404");
        }
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("actor", $results[0]);
        $template->set("view", new Template(PATH_VIEWS."tests/actors_detail.html"));
        $response->setOutput($template->parse());
        return $response;
    }

	/**
     *
	 * @return HtmlResponse
	 * @throws SystemException
	 */
    #[Route("tinymce", HTTP_GET)]
    public function tinymce() : AResponse {
		if( !empty($_POST) && isset($_POST["content"]) ) {
			print_debug($_POST["content"]);
		}
		$response = new HtmlResponse();
		$template = new Template(PATH_VIEWS . "template.html");
		$template->set("view", new Template(PATH_VIEWS . "tests/tinymce.html"));
		$response->setOutput($template->parse());
		return $response;
	}

}
