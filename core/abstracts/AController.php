<?php

namespace core\abstracts;

use core\classes\Router;

abstract class AController {

	abstract public function initRoutes( Router $router ): void;

	public function __toString(): string {
		return __CLASS__;
	}
}