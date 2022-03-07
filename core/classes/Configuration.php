<?php

namespace core\classes;

/**
 * The Configuration type singleton
 * Collect all the configurations and stores them in an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Configuration {

	// the instance of this class
	private static ?Configuration $_instance = null;
	// an array holding all configurations
	private array $_config;

	/**
	 * The class constructor
	 * will be called once by the static method getInstance()
	 * Parses the {configuration}.ini
	 */
	private function __construct() {
		$this->_config = parse_ini_file(PATH_ROOT."application.ini", true);
	}

	/**
	 * The initializer for this class
	 *
	 * @return Configuration
	 */
	public static function getInstance(): Configuration {
		if( static::$_instance === null ) {
			static::$_instance = new Configuration();
		}
		return static::$_instance;
	}

	/**
	 * Returns the whole configuration
	 *
	 * @return array
	 */
	public function getConfig(): array {
		return $this->_config;
	}

	/**
	 * Returns a specific section of the configuration
	 *
	 * @param string $name
	 * @return array
	 */
	public function getSection( string $name ): array {
		return $this->_config[$name] ?? array();
	}
}