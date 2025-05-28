<?php

namespace controller\www;

use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;

/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("/")]
class HomeController extends AController {

	/**
	 * Shows the landing page
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("", RequestMethod::GET)]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "home/index.html");
		TemplateData::set("test", "hallo");
		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());
		return new HtmlResponse($template->parse());
	}

}
