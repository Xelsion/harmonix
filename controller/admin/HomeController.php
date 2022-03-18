<?php

namespace controller\admin;

use system\abstracts\AResponse;
use system\abstracts\AController;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

	/**
	 * @param Router $router
	 * @see \system\interfaces\IController
	 */
	public function init( Router $router ): void {
		// Add routes to router
		$router->addRoute("/", __CLASS__);

		// Add MenuItems to the Menu
        $this::$_menu->insertMenuItem(100, null, "Home", "/");
	}

	/**
	 * @return AResponse
	 * @see \system\interfaces\IController
	 */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", $this::$_menu);
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}