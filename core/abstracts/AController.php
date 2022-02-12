<?php

namespace core\abstracts;

use core\interfaces\IController;

abstract class AController implements IController {

	private string $_route;

	public function setRoute( string $route ): void {
		$this->_route = $route;
	}

	public function getRoute(): string {
		return $this->_route;
	}

}