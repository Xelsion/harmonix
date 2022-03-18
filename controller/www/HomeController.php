<?php

namespace controller\www;

use system\abstracts\AResponse;
use system\abstracts\AController;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;

/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

	public function __construct() {

	}

	public function init( Router $router ): void {
		$router->addRoute("/", __CLASS__);

        $this::$_menu->insertMenuItem(100, null, "Home", "/");
	}

	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}