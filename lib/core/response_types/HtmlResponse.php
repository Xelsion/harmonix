<?php

namespace lib\core\response_types;

use lib\core\blueprints\AResponse;
use lib\core\enums\HttpResponseCode;

/**
 * A Response for HTML content
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HtmlResponse extends AResponse {

	// the default status for html status headers
	public HttpResponseCode $status_code;

	/**
	 * The class constructor
	 *
	 * @param string $content
	 */
	public function __construct(string $content = "") {
		if( $content !== "" ) {
			$this->setOutput($content);
		}
		$this->status_code = HttpResponseCode::Ok;
	}

	/**
	 * Sets the headers status code of this response
	 * @param int $status_code
	 * @return void
	 */
	public function withHeader(HttpResponseCode $status_code): void {
		$this->status_code = $status_code;
	}

	/**
	 * @inherite
	 */
	public function setHeaders(): void {
		header($this->status_code->toString());
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Content-Type: text/html; charset=iso-8859-1");
	}

}
