<?php

namespace lib\core\attributes;

use Attribute;
use lib\core\enums\RequestMethod;

/**
 * The Attribute class Route.
 * This class is used to define Route within a controller class.
 * The Router class uses this attribute to register all routes
 *
 * @see Route
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
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