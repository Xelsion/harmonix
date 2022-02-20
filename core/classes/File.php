<?php

namespace core\classes;

/**
 * The File class
 */
class File {

	// The file path
	protected ?string $_file_path = null;
	// The file content
	protected ?string $_content = null;

	/**
	 * The constructor
	 * Sets the file path
	 *
	 * @param string $file_path
	 */
	public function __construct( string $file_path ) {
		$this->_file_path = $file_path;
	}

	/**
	 * Checks if the current file exists
	 * @return bool
	 */
	public function exists(): bool {
		return file_exists($this->_file_path);
	}

	/**
	 * Sets the file content
	 *
	 * @param string $content
	 */
	public function setContent( string $content ): void {
		$this->_content = $content;
	}

	/**
	 * Returns the content of the file
	 *
	 * @return string
	 */
	public function getContent(): string {
		if( is_null($this->_content) ) {
			$this->read();
		}
		return $this->_content;
	}

	/**
	 * Checks if the file exists and reads its content if it does.
	 * Return true if successful and false if not
	 *
	 * @return bool
	 */
	public function read(): bool {
		if( $this->exists() && is_readable($this->_file_path) ) {
			$this->_content = file_get_contents($this->_file_path);
			return true;
		}
		return false;
	}

	/**
	 * Checks if the current file is writeable and writes the current content
	 * to the file.
	 * Return true if successful and false if not
	 *
	 * @return bool
	 */
	public function save(): bool {
		if( is_writable($this->_file_path) ) {
			file_put_contents($this->_file_path, $this->_content);
			return true;
		}
		return true;
	}

	/**
	 * Tries to save the current content to the given file path
	 * Return true if successful and false if not
	 *
	 * @param string $file_path
	 * @return bool
	 */
	public function saveAs( string $file_path ): bool {
		if( file_put_contents($file_path, $this->_content) ) {
			return true;
		}
		return false;
	}
}