<?php

namespace core\classes;

class Template extends File {

	private array $_data = array();

	public function __construct( string $file_path ) {
		parent::__construct($file_path);
	}

	public function addParam( $name, $value ): void {
		$this->_data[$name] = $value;
	}

	public function getParam( $name ) {
		return $this->_data[$name] ?? null;
	}

	public function parse(): string {
		ob_start();
		require( $this->_file_path );
		return ob_get_clean();
	}
}