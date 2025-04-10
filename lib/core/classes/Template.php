<?php

namespace lib\core\classes;

use lib\core\exceptions\SystemException;

/**
 * The Template class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Template extends TemplateData {

	private string $file_path;

	/**
	 * The constructor
	 * Sets the file path of the template
	 * Throws an exception if the file was not found
	 *
	 * @param string $file_path
	 *
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function __construct(string $file_path) {
		if( !file_exists($file_path) ) {
			throw new SystemException(__FILE__, __LINE__, "Template: file[" . $file_path . "] not found!");
		}
		$this->file_path = $file_path;
	}

	/**
	 * Returns the filepath of the template
	 *
	 * @return string
	 */
	public function getFilePath(): string {
		return $this->file_path;
	}

	/**
	 * Includes the template file and returns its output
	 *
	 * @return string
	 */
	public function parse(): string {
		ob_start();
		require($this->file_path);
		return ob_get_clean();
	}
	
}
