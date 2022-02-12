<?php

namespace core\interfaces;

interface IController {

	public function setRoute( string $route ): void;

	public function getRoute(): string;

}