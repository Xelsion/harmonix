<?php

namespace lib\core\attributes;

use Attribute;
use lib\core\enums\RequestMethod;

/**
 * The Attribute class HttpGet.
 * This class defines the GET type of the Route
 *
 * @see Route
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Attribute]
class HttpGet extends Route {

	public function __construct(string $path) {
		parent::__construct($path, RequestMethod::GET);
	}

}