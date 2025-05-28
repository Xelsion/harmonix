<?php

namespace lib\core\attributes;

use Attribute;
use lib\core\enums\RequestMethod;

/**
 * The Attribute class HttpPost.
 * This class defines the POST type of the Route
 *
 * @see Route
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Attribute]
class HttpPost extends Route {

	public function __construct(string $path) {
		parent::__construct($path, RequestMethod::POST);
	}

}