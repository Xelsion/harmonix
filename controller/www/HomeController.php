<?php

namespace controller\www;

use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
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
	 * @return \lib\core\blueprints\AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("")]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "home/index.html");
		$view->set("test", "hallo");

		$template = new Template(PATH_VIEWS . "template.html");
		$template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

}
