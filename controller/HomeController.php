<?php

namespace controller;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\classes\ResponseHTML;
use core\classes\Router;

class HomeController extends AController {

	public function __construct() {
	}

	public function initRoutes( Router $router ): void {
		$router->addRoute("/", __CLASS__."->indexAction");
		$router->addRoute("/list", __CLASS__."->listAction");
		$router->addRoute("/list/{page}", __CLASS__."->listDetailAction");
	}

	public function indexAction(): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from indexAction!");
		return $response;
	}

	public function listAction(): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from listAction");
		return $response;
	}

	public function listDetailAction( int $page ): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from listDetailAction Page[".$page."]!");
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}