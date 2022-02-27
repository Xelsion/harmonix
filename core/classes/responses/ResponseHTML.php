<?php

namespace core\classes\responses;

use core\abstracts\AResponse;

/**
 * A Response for HTML content
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ResponseHTML extends AResponse {

	public function setHeaders(): void {
		header("Content-Type: text/html");
	}

}