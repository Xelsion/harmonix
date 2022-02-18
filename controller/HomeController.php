<?php

namespace controller;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\classes\ResponseHTML;

class HomeController extends AController {

	public function __construct() {
		$this->setRoute("/");
		$this->addActionFunction("", "indexAction");
	}

	public function indexAction(): AResponse {
		$response = new ResponseHTML();
		$response->setOutput("Hallo from indexAction!");
		return $response;
	}

}