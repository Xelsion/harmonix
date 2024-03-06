<?php

namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\cache\types\ResponseCache;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\core\Router;

#[Route("tests")]
class TestController extends AController {

	/**
	 * Shows the starting page of the test controller
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::GET)]
	public function index(): AResponse {
		$cache = App::getInstanceOf(ResponseCache::class);
		$cache->initCacheFor(__METHOD__);
		$cache->addFileCheck(__FILE__);
		$cache->addFileCheck(PATH_VIEWS . "template.html");
		$cache->addFileCheck(PATH_VIEWS . "test/index.html");

		if( $cache->isUpToDate() ) {
			$content = $cache->getContent();
		} else {
			$all_routes = array();
			(App::getInstanceOf(Router::class))->getAllRoutes(PATH_CONTROLLER_ROOT, $all_routes);

			$view = new Template(PATH_VIEWS . "test/index.html");
			TemplateData::set("routes_list", $all_routes);

			$template = new Template(PATH_VIEWS . "template.html");
			TemplateData::set("view", $view->render());

			$content = $template->render();

			$cache->saveContent($content);
		}

		return new HtmlResponse($content);
	}
}
