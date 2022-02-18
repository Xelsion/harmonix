<?php

namespace core\classes;

class File {

	protected ?string $_file_path = null;
	protected ?string $_content = null;

	public function __construct( string $file_path ) {
		if( file_exists($file_path) ) {
			$this->_file_path = $file_path;
		}
	}

	public function setContent( string $content ): void {
		$this->_content = $content;
	}

	public function getContent(): string {
		if( is_null($this->_content) ) {
			$this->read();
		}
		return $this->_content;
	}

	public function read(): bool {
		if( is_readable($this->_file_path) ) {
			$this->_content = file_get_contents($this->_file_path);
			return true;
		}
		return false;
	}

	public function save(): bool {
		if( is_writable($this->_file_path) ) {
			file_put_contents($this->_file_path, $this->_content);
			return true;
		}
		return true;
	}

	public function saveAs( string $file_path ): bool {
		if( file_put_contents($file_path, $this->_content) ) {
			return true;
		}
		return false;
	}
}