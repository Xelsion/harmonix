<?php

namespace core\classes;

use RuntimeException;

/**
 * The Template class
 */
class Template extends File {

	// the data storage for the template
	private array $_data = array();

	/**
	 * The constructor
	 * Sets the file path of the template
	 * Throws an exception if the file was not found
	 *
	 * @param string $file_path
	 * @throws RuntimeException
	 */
	public function __construct( string $file_path ) {
		parent::__construct($file_path);
		if( !$this->exists() ) {
			throw new RuntimeException("Template: file[".$file_path."] not found!");
		}
	}

	/**
	 * Adds a key => value pair to the data storage
	 *
	 * @param $key
	 * @param $value
	 */
	public function addParam( $key, $value ): void {
		$this->_data[$key] = $value;
	}

	/**
	 * Returns the value vom the data storage by the given key
	 * or null if the key was not found.
	 *
	 * @param $key
	 * @return mixed|null
	 */
	public function getParam( $key ) {
		return $this->_data[$key] ?? null;
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