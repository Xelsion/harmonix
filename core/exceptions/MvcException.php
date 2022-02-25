<?php

namespace core\exceptions;

class MvcException extends \RuntimeException {

	public function __construct( string $file, string $line, string $message ) {
		parent::__construct($message);
	}

}