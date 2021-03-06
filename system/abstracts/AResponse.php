<?php

namespace system\abstracts;

use system\interfaces\IResponse;

/**
 * The Abstract version of a Response
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AResponse {

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

    /**
     * sets the headers for the response
     */
    abstract public function setHeaders(): void;

}