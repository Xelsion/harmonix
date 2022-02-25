<?php

namespace core\classes;

use core\abstracts\AResponse;

/**
 * A Response for HTML content
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ResponseHTML extends AResponse {

	private string $_output;

	public function setOutput( string $output ): void {
		$this->_output = $output;
	}

	public function getOutput(): string {
		return $this->_output;
	}
}