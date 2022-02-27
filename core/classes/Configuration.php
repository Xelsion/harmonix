<?php

namespace core\classes;

class Configuration {

	private static ?Configuration $_instance = null;
	private array $_config;

	private function __construct() {
		$this->_config = parse_ini_file(PATH_ROOT."application.ini", true);
	}

	public static function getInstance(): Configuration {
		if( static::$_instance === null ) {
			static::$_instance = new Configuration();
		}
		return static::$_instance;
	}

	public function getConfig(): array {
		return $this->_config;
	}

	public function getSection( string $name ): array {
		return $this->_config[$name] ?? array();
	}
}