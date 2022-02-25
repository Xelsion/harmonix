<?php

namespace controller;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\System;
use core\classes\Logger;
use core\classes\ResponseHTML;
use core\classes\Router;
use core\classes\Template;
use models\Actor;

/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

	private Logger $_logger;

	public function __construct() {

	}

	public function init(): void {
		$system = System::getInstance();
		$this->_logger = $system->logger;
	}

	public function initRoutes( Router $router ): void {
		$router->addRoute("/", __CLASS__."->indexAction");
		$router->addRoute("/list", __CLASS__."->listAction");
		$router->addRoute("/actors", __CLASS__."->testAction");
		$router->addRoute("/actors/{id}", __CLASS__."->testDetailAction");
	}

	public function indexAction(): AResponse {
		$this->_logger->log(__CLASS__."::indexAction", __LINE__, "Hallo");
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

	public function testDetailAction( Actor $actor ): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from testDetailAction key[]!");
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}