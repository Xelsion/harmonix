<?php

namespace lib\core\attributes;

use Attribute;
use lib\core\enums\RequestMethod;

#[Attribute]
class HttpDelete extends Route {

	public function __construct(string $path) {
		parent::__construct($path, RequestMethod::DELETE);
	}

}