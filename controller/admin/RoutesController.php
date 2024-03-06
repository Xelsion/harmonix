<?php

namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\LinqList;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\core\Router;

#[Route("routes")]
class RoutesController extends AController {

	/**
	 * Get a list of all Routes
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::GET)]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "routes/index.html");
		$conflicts = App::getInstanceOf(Router::class)->checkForConflicts();
		$linq_list = new LinqList($conflicts);
		TemplateData::set("routes_list", App::getInstanceOf(Router::class)->getSortedRoutes());
		TemplateData::set("conflicts", $linq_list);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->render());

		$content = $template->render();
		return new HtmlResponse($content);
	}

}
