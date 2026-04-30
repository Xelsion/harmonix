<?php

namespace lib\core\abstracts;

use lib\core\classes\Template;

/**
 * The Abstract version of a Response
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AResponse {

	// The output content
	public ?Template $template;

	private string $output;

	public function parseTemplate(): void {
		if( $this->template !== null ) {
			$this->output = $this->template->parse();
		}
	}

	/**
	 * Sets the output content of the response_types
	 *
	 * @param string $output
	 */
	public function setOutput(string $output): void {
		$this->output = $output;
	}

	/**
	 * Returns the content of the response_types
	 *
	 * @return string
	 */
	public function getOutput(): string {
		return $this->output;
	}

	/**
	 * sets the headers for the response_types
	 */
	abstract public function setHeaders(): void;

}
