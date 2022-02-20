<?php

namespace controller;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\classes\ResponseHTML;
use core\classes\Router;
use core\classes\Template;

class HomeController extends AController {

	public function __construct() {
	}

	public function initRoutes( Router $router ): void {
		$router->addRoute("/", __CLASS__."->indexAction");
		$router->addRoute("/list", __CLASS__."->listAction");
		$router->addRoute("/products", __CLASS__."->testAction");
		$router->addRoute("/products/{page}", __CLASS__."->testDetailAction");
	}

	public function indexAction(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.phtml");
		$template->addParam("view", new Template(PATH_VIEWS."home/index.phtml"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function listAction( int $page = 1 ): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from listAction Page[".$page."]!");
		return $response;
	}

	public function testAction(): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from testAction!");
		return $response;
	}

	public function testDetailAction( string $key ): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from testDetailAction key[".$key."]!");
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}