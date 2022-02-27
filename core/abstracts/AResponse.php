<?php

namespace core\abstracts;

use core\interfaces\IResponse;

/**
 * The Abstract version of a Response
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AResponse implements IResponse {

	// The output content
	private string $_output;

	/**
	 * Sets the output content of the response
	 *
	 * @param string $output
	 */
	public function setOutput( string $output ): void {
		$this->_output = $output;
	}

	/**
	 * Returns the content of the response
	 *
	 * @return string
	 */
	public function getOutput(): string {
		return $this->_output;
	}

}