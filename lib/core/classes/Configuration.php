<?php

namespace lib\core\classes;

use lib\core\exceptions\SystemException;

/**
 * The Configuration type setAsSingleton
 * Collect all the configurations and stores them in an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Configuration extends File {

	// an array holding all configurations
	private array $config;

	/**
	 * The class constructor
	 * will be called once by the static method getInstanceOf()
	 * Parses the {configuration}.ini
	 */
	public function __construct(string $file_path) {
		parent::__construct($file_path);
		$this->config = parse_ini_file($this->file_path, true, INI_SCANNER_TYPED);
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
	 * Sets the whole configuration with the given data
	 *
	 * @param array $config
	 * @return void
	 */
	public function setConfig(array $config): void {
		$this->config = [];
		foreach( $config as $section => $entries ) {
			foreach( $entries as $name => $entry ) {
				if( is_array($entry) ) {
					foreach( $entry as $key => $value ) {
						if( $value === "true" ) {
							$value = true;
						} else if( $value === "false" ) {
							$value = false;
						}
						$this->config[$section][$name][$key] = $value;
					}
				} else {
					if( $entry === "true" ) {
						$entry = true;
					} else if( $entry === "false" ) {
						$entry = false;
					}
					$this->config[$section][$name] = $entry;
				}
			}
		}
	}

	/**
	 * @return bool
	 * @throws SystemException
	 */
	public function writeConfig(): bool {
		$content = "";
		foreach( $this->config as $section => $entries ) {
			$content .= "[{$section}]\n";
			foreach( $entries as $name => $values ) {
				if( is_array($values) ) {
					foreach( $values as $key => $value ) {
						$value = $this->getFormattedWriteValue($value);
						$content .= "{$name}[{$key}] = {$value};\n";
					}
					$content .= "\n";
				} else {
					$value = $this->getFormattedWriteValue($values);
					$content .= "{$name} = {$value};\n";
				}
			}
			$content .= "\n";
		}
		$this->setContent($content);
		return $this->save();
	}

	/**
	 * Returns a specific section of the configuration
	 *
	 * @param string $name
	 * @return array
	 */
	public function getSection(string $name): array {
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
	public function getSectionValue(string $name, string $key): mixed {
		$section = $this->getSection($name);
		if( empty($section) || !array_key_exists($key, $section) ) {
			return null;
		}
		return $section[$key];
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function getFormattedWriteValue(mixed $value): mixed {
		if( is_bool($value) ) {
			return ($value) ? 'true' : 'false';
		}
		if( $value === "true" || $value === "false" || preg_match("/^[0-9|.]+$/", $value) ) {
			return $value;
		}
		return '"' . $value . '"';
	}

}
