<?php

namespace lib\core\classes;

use lib\core\enums\MimeType;
use lib\core\exceptions\SystemException;

/**
 * The File class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class File {

	// The file path
	public string $file_path = "";

	// Name of the file
	public string $file_name = "";

	// The file content
	public string $file_content = "";

	// The files mime type
	public string $mime_type = "";

	// The file size in bytes
	public float $file_size = 0.0;

	/**
	 * The constructor
	 * Sets the file path
	 *
	 * @param string $file_path
	 */
	public function __construct(string $file_path = "") {
		$this->file_path = $file_path;
		if( $file_path !== "" && file_exists($file_path) ) {
			$this->file_name = basename($file_path);
			if( function_exists("mime_content_type") && is_callable("mime_content_type") ) {
				$this->mime_type = mime_content_type($file_path);
			} else {
				$this->mime_type = $this->getMimeType();
			}
			$this->file_size = filesize($file_path);
			$this->read();
		}

	}

	/**
	 * Checks if the current file exists
	 * @return bool
	 */
	public function exists(): bool {
		return file_exists($this->file_path);
	}

	/**
	 * @param string $content
	 * @return $this
	 */
	public function setContent(string $content): File {
		if( $this->isBase64($content) ) {
			$this->file_content = base64_decode($content);
		} else {
			$this->file_content = $content;
		}
		$this->file_size = strlen($this->file_content) * 0.67;
		return $this;
	}

	/**
	 * Returns the content of the file
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->file_content;
	}

	/**
	 * Checks if the file exists and reads its content if it does.
	 * Return true if successful and false if not
	 *
	 * @return File
	 */
	public function read(): File {
		if( $this->exists() && is_readable($this->file_path) ) {
			$this->file_content = file_get_contents($this->file_path);
		}
		return $this;
	}

	/**
	 * writes the current content to the current file.
	 * Return true if successful and false if not
	 *
	 * @return bool
	 * @throws SystemException
	 */
	public function save(): bool {
		$path_parts = pathinfo($this->file_path);
		$this->createIfNotExists($path_parts["dirname"]);
		return file_put_contents($this->file_path, $this->file_content);
	}

	/**
	 * Tries to save the current content to the given file path.
	 * Return true if successful and false if not
	 *
	 * @param string $file_path
	 * @return bool
	 * @throws SystemException
	 */
	public function saveAs(string $file_path): bool {
		$path_parts = pathinfo($file_path);
		$this->createIfNotExists($path_parts["dirname"]);
		return file_put_contents($file_path, $this->file_content);
	}

	/**
	 * Adds the given $content to the end of the files content.
	 * Return true if successful and false if not
	 *
	 * @param string $content
	 * @return bool
	 * @throws SystemException
	 */
	public function append(string $content): bool {
		$path_parts = pathinfo($this->file_path);
		$this->createIfNotExists($path_parts["dirname"]);
		return file_put_contents($this->file_path, $content, FILE_APPEND);
	}

	/**
	 * Deletes the current file from the disc
	 *
	 * @return void
	 */
	public function delete(): void {
		if( file_exists($this->file_path) ) {
			unlink($this->file_path);
		}
	}

	/**
	 * Returns the timestamp of the last file change
	 *
	 * @return int
	 */
	public function getLastModified(): int {
		if( $this->exists() ) {
			$ctime = filectime($this->file_path);
			$mtime = filemtime($this->file_path);
			if( $mtime && $mtime > $ctime ) {
				return $mtime;
			}
			return $ctime;
		}
		return 0;
	}

	/**
	 * Returns the mime-type of the current file using the getMimeTypeByContent method
	 * if it fails using the getMimeTypeByExtension method.
	 *
	 * @return string
	 */
	public function GetMimeType(): string {
		$mime_type = $this->getMimeTypeByContent($this->file_path);
		if( $mime_type === "unknown" ) {
			$mime_type = $this->getMimeTypeByExtension($this->file_name);
		}
		return $mime_type;
	}

	/**
	 * Returns the mime-type by the files extension (not recommend)
	 *
	 * @param string $file_name
	 * @return string
	 */
	public function getMimeTypeByExtension(string $file_name): string {
		$file_parts = explode('.', $file_name);
		$extension = array_pop($file_parts);
		return MimeType::fromExtension($extension)->toString();
	}

	/**
	 * Returns the mime-type by the content of the file (recommend)
	 *
	 * requires one of the following function: 'mime_content_type' or 'finfo_open'
	 *
	 * @param string $file_path
	 * @return string
	 */
	public function getMimeTypeByContent(string $file_path): string {
		$mime_type = "unknown";
		if( function_exists("mime_content_type") && is_callable("mime_content_type") ) {
			$mime_type = mime_content_type($file_path);
		} else if( function_exists("finfo_open") && is_callable("finfo_open") ) {
			$file_info = finfo_open(FILEINFO_MIME_TYPE);
			$mime_type = finfo_file($file_info, $file_path);
		}
		return $mime_type;
	}

	/**
	 * Checks if the mime-type given by the files extension and
	 * the mime-type given by the files content are the same
	 *
	 * @return bool
	 */
	public function hasCorrectMimeType(): bool {
		$mime_type_by_ext = $this->GetMimeTypeByExtension($this->file_name);
		$mime_type_by_content = $this->getMimeTypeByContent($this->file_path);
		return ($mime_type_by_ext === $mime_type_by_content);
	}

	/**
	 * Checks if the given string is a base64 string
	 *
	 * @param string $content
	 * @return bool
	 */
	private function isBase64(string $content): bool {
		return (base64_encode(base64_decode($content, true)) === $content);
	}

	/**
	 * Creates all directories in the given path if not exists
	 *
	 * @param string $path
	 * @return void
	 * @throws SystemException
	 */
	public function createIfNotExists(string $path): void {
		if( !file_exists($path) && !mkdir($path, 0660, true) && !is_dir($path) ) {
			throw new SystemException(__FILE__, __LINE__, sprintf("Cant create Directory '%s'", $path));
		}
	}

}
