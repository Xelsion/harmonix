<?php

namespace system\classes;

use system\exceptions\SystemException;

/**
 * The File class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
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
     * Adds the given $content to the end of the files content.
     * Return true if successful and false if not
     *
     * @param string $content
     * @return bool
     * @throws SystemException
     */
	public function append( string $content ): bool {
        $path_parts = pathinfo($this->_file_path);
        // Create all necessary folders
        if( !file_exists($path_parts["dirname"]) && !mkdir($path_parts["dirname"], 0777, true) && !is_dir($path_parts["dirname"]) ) {
            throw new SystemException(__FILE__, __LINE__, sprintf('Directory "%s" was not created', $path_parts["dirname"]));
        }

		return file_put_contents($this->_file_path, $content, FILE_APPEND);
	}

    /**
     * writes the current content to the current file.
     * Return true if successful and false if not
     *
     * @return bool
     * @throws SystemException
     */
	public function save(): bool {
        $path_parts = pathinfo($this->_file_path);
        // Create all necessary folders
        if( !file_exists($path_parts["dirname"]) && !mkdir($path_parts["dirname"], 0777, true) && !is_dir($path_parts["dirname"]) ) {
            throw new SystemException(__FILE__, __LINE__, sprintf('Directory "%s" was not created', $path_parts["dirname"]));
        }

		return file_put_contents($this->_file_path, $this->_content);
	}

    /**
     * Deletes the current file from the disc
     *
     * @return void
     */
    public function delete() : void {
        if( file_exists($this->_file_path) ) {
            unlink($this->_file_path);
        }
    }

	/**
	 * Tries to save the current content to the given file path.
	 * Return true if successful and false if not
	 *
	 * @param string $file_path
	 * @return bool
	 */
	public function saveAs( string $file_path ): bool {
		return file_put_contents($file_path, $this->_content);
	}
}