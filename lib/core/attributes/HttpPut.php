<?php

namespace lib\core\attributes;

use Attribute;
use lib\core\enums\RequestMethod;

/**
 * The Attribute class HttpPut.
 * This class defines the PUT type of the Route
 *
 * @see Route
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Attribute]
class HttpPut extends Route {

	public function __construct(string $path) {
		parent::__construct($path, RequestMethod::PUT);
	}

}