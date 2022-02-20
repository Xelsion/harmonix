<?php

namespace core\interfaces;

use core\abstracts\AResponse;
use core\classes\Router;

interface IController {

	public function initRoutes( Router $router ): void;

	public function indexAction(): AResponse;

}