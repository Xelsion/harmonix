<?php

namespace controller\www;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\classes\responses\ResponseHTML;
use core\classes\Router;
use core\classes\Template;
use core\System;
use models\Actor;


/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

	public function __construct() {

	}

	public function initRoutes( Router $router ): void {
		$router->addRoute("/", __CLASS__."->indexAction");
		$router->addRoute("/list", __CLASS__."->listAction");
		$router->addRoute("/actors", __CLASS__."->testAction");
		$router->addRoute("/actors/{id}", __CLASS__."->testDetailAction");
	}

	public function indexAction(): AResponse {
		$actor = System::getInstance()->getActor();

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