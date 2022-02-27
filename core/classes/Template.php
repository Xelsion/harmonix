<?php

namespace core\classes;

use RuntimeException;

/**
 * The Template class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Template extends TemplateData {

	private string $_file_path;

	/**
	 * The constructor
	 * Sets the file path of the template
	 * Throws an exception if the file was not found
	 *
	 * @param string $file_path
	 * @throws RuntimeException
	 */
	public function __construct( string $file_path ) {
		if( !file_exists($file_path) ) {
			throw new RuntimeException("Template: file[".$file_path."] not found!");
		}
		$this->_file_path = $file_path;
	}

	/**
	 * Includes the template file and returns its output
	 *
	 * @return string
	 */
	public function parse(): string {
		ob_start();
		require( $this->_file_path );
		return ob_get_clean();
	}
}