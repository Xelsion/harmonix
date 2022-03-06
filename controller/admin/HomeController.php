<?php

namespace controller\admin;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\classes\responses\ResponseHTML;
use core\classes\Router;
use core\classes\Template;
use core\classes\tree\Menu;
use core\classes\tree\MenuItem;

/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

	public function init( Router $router ): void {
		static::$_menu->insertMenuItem(100, null, "Home", "/");
		$router->addRoute("/", __CLASS__);
	}

	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", static::$_menu);
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}