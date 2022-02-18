<?php

namespace core\classes;

use core\abstracts\AResponse;

class ResponseHTML extends AResponse {

	private string $_output;

	public function setOutput( string $output ): void {
		$this->_output = $output;
	}

	public function getOutput(): string {
		return $this->_output;
	}
}