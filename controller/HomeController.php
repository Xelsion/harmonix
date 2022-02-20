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
		$router->addRoute("/list/{page}", __CLASS__."->listAction");
	}

	public function indexAction(): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from indexAction!");
		return $response;
	}

	public function listAction( string $page = "1" ): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from listAction Page[".$page."]!");
		return $response;
	}

}