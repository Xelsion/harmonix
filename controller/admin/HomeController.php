<?php

namespace controller\admin;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\classes\responses\ResponseHTML;
use core\classes\Router;
use core\classes\Template;
use core\Core;

/**
 * @see \core\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

	/**
	 * @param Router $router
	 * @see \core\interfaces\IController
	 */
	public function init( Router $router ): void {
		// Add routes to router
		$router->addRoute("/", __CLASS__);

		// Add MenuItems to the Menu
		Core::$_menu->insertMenuItem(100, null, "Home", "/");
	}

	/**
	 * @return AResponse
	 * @see \core\interfaces\IController
	 */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", Core::$_menu);
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}