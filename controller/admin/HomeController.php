<?php

namespace controller\admin;

use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;

/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("/")]
class HomeController extends AController {

	/**
	 * Get the starting site
	 *
	 * @return \lib\core\blueprints\AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("", RequestMethod::GET, RequestMethod::POST)]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "home/index.html");
		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse(), true);
		return new HtmlResponse($template);
	}

}
