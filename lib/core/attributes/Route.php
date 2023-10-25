<?php

namespace lib\core\attributes;

use Attribute;
use lib\core\enums\RequestMethod;

#[Attribute]
class Route {

	public string $path;

	public array $methods;

	public function __construct(string $path, RequestMethod ...$methods) {
		$this->path = $path;
		foreach( $methods as $method ) {
			$this->methods[] = $method->toString();
		}
	}

}