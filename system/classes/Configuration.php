<?php

namespace system\classes;

/**
 * The Configuration type singleton
 * Collect all the configurations and stores them in an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Configuration {

	// the instance of this class
	private static ?Configuration $instance = null;
	// an array holding all configurations
	private array $config;

	/**
	 * The class constructor
	 * will be called once by the static method getInstance()
	 * Parses the {configuration}.ini
	 */
	private function __construct() {
		$this->config = parse_ini_file(PATH_ROOT."application.ini", true);
	}

	/**
	 * The initializer for this class
	 *
	 * @return Configuration
	 */
	public static function getInstance(): Configuration {
		if( static::$instance === null ) {
			static::$instance = new Configuration();
		}
		return static::$instance;
	}

	/**
	 * Returns the whole configuration
	 *
	 * @return array
	 */
	public function getConfig(): array {
		return $this->config;
	}

	/**
	 * Returns a specific section of the configuration
	 *
	 * @param string $name
	 * @return array
	 */
	public function getSection( string $name ): array {
		return $this->config[$name] ?? array();
	}

    /**
     * Returns a specific value in a specific section
     *
     * @param string $name
     * @param string $key
     *
     * @return mixed
     */
    public function getSectionValue( string $name, string $key): mixed {
        $section = $this->getSection($name);
        if( empty($section) || !array_key_exists($key, $section) ) {
            return null;
        }
        return $section[$key];
    }

}
