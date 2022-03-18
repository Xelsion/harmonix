<?php

namespace system\classes\responses;

use system\abstracts\AResponse;

/**
 * A Response for HTML content
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ResponseHTML extends AResponse {

	// the default status for html status headers
	public int $status_code = 200;

	/**
	 * @inherite
	 */
	public function setHeaders(): void {
		header("Content-Type: text/html; charset=utf-8");
		switch( $this->status_code ) {
			case 403:
				header("HTTP/1.1 403 Forbidden");
				break;
			case 404:
				header("HTTP/1.1 404 Not Found");
				break;
			default:
				header("HTTP/1.1 200 OK");
		}
	}

}