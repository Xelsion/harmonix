<?php

namespace controller\admin;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ErrorController extends AController {

	/**
	 * @param Router $router
	 * @see \system\interfaces\IController
	 */
	public function init( Router $router ): void {
		// Add routes to router
		$router->addRoute("/error/{error_code}", __CLASS__, "error");
	}

	/**
	 * @see \system\interfaces\IController
	 * @return AResponse
	 */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", $this::$_menu);
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function error( int $error_code ): AResponse {
		$response = new ResponseHTML();
		$response->status_code = $error_code;
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", $this::$_menu);
		$template->set("view", new Template(PATH_VIEWS."error/".$error_code.".html"));
		$response->setOutput($template->parse());
		return $response;
	}
}