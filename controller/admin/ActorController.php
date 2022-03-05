<?php

namespace controller\admin;

use core\abstracts\AController;
use core\abstracts\AResponse;
use core\classes\responses\ResponseHTML;
use core\classes\Router;
use core\classes\Template;

class ActorController extends AController {

	public function init( Router $router ): void {
		static::$_menu->insertMenuItem(2, null, "Benutzer", "/actors");
		$router->addRoute("/actors", __CLASS__);
	}

	public function indexAction(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", static::$_menu);
		$template->set("view", new Template(PATH_VIEWS."actor/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}