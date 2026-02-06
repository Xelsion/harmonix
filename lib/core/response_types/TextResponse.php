<?php

namespace lib\core\response_types;

use lib\core\blueprints\AResponse;
use lib\core\enums\HttpResponseCode;

class TextResponse extends AResponse {

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
	 * Sets the header status code of this response
	 * @param HttpResponseCode $status_code
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
		header("Content-Type: text/plain; charset=UTF-8");
	}

}