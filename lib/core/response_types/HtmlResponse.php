<?php

namespace lib\core\response_types;

use lib\core\abstracts\AResponse;
use lib\core\classes\Template;
use lib\core\enums\HttpResponseCode;

/**
 * A Response for HTML content
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HtmlResponse extends AResponse {

	// the default status for HTML status headers
	public HttpResponseCode $status_code;

	/**
	 * The class constructor
	 *
	 * @param Template $template
	 */
	public function __construct(Template $template) {
		$this->template = $template;
		$this->status_code = HttpResponseCode::Ok;
	}

	public function parseResponse(): void {
		$this->setOutput($this->template->parse());
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
		header("Content-Type: text/html; charset=UTF-8");
	}

}
