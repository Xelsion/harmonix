<?php

namespace controller\www;

use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\HttpResponseCode;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;

/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("error")]
class ErrorController extends AController {

	/**
	 *  Shows the error page with the given code
	 *
	 * @param int $error_code
	 *
	 * @return \lib\core\blueprints\AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("{error_code}", RequestMethod::ANY)]
	public function error(int $error_code): AResponse {
		$response_code = HttpResponseCode::fromCode($error_code);
		$view = new Template(PATH_VIEWS . "error/display.html");
		TemplateData::set("title", $response_code->toString());

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->render());

		$response = new HtmlResponse($template->render());
		$response->status_code = $response_code;
		return $response;
	}
}
