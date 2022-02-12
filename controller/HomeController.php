<?php

namespace controller;

use core\interfaces\IController;
use core\abstracts\AController;

class HomeController extends AController {

	public function __construct() {
		$this->setRoute("/");
	}

}