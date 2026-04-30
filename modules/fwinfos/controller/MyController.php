<?php

namespace modules\fwinfos\controller;

use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;

#[Route("/fwinfo")]
class MyController extends AController {

	/**
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("", RequestMethod::GET)]
	public function index(): AResponse {
		$template = new Template(PATH_VIEWS . "template.html");
		$view = new Template(PATH_MODULES . "fwinfos" . DIRECTORY_SEPARATOR . "templates/index.html");
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
	}

}