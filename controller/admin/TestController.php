<?php

namespace controller\admin;

use lib\App;
use lib\core\abstracts\AController;
use lib\core\abstracts\AResponse;
use lib\core\attributes\Route;
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
		$all_routes = array();
		(App::getInstanceOf(Router::class))->getAllRoutes(PATH_CONTROLLER_ROOT, $all_routes);

		$view = new Template(PATH_VIEWS . "test/index.html");
		TemplateData::set("routes_list", $all_routes);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse(), true);
		return new HtmlResponse($template);
	}
}
