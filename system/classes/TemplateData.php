<?php

namespace system\classes;

use system\Core;

/**
 * This class can hold key => value pairs that
 * will be shared by all used templates
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TemplateData {

	// the data storage for the template
	private static array $_data = array();

	/**
	 * Adds a key => value pair to the data storage
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value ): void {
		static::$_data[$key] = $value;
	}

	/**
	 * Returns the value vom the data storage by the given key
	 * or null if the key was not found.
	 *
	 * @param $key
	 * @return mixed|null
	 */
	public function get( $key ) {
		return static::$_data[$key] ?? null;
	}

	/**
	 * Adds the value to an array the array $name at $index to the data storage
	 *
	 * @param string $name
	 * @param $value
	 * @param null $index
	 */
	public function toArray( string $name, $value, $index = null ): void {
		if( !is_null($index) ) {
			static::$_data[$name][$index] = $value;
		} else {
			static::$_data[$name][] = $value;
		}
	}

	/**
	 * Returns the value from an array in the data storage by the given $name
	 * and $index or null if the key was not found.
	 *
	 * @param $name
	 * @param $index
	 * @return mixed|null
	 */
	public function fromArray( $name, $index ) {
		return static::$_data[$name][$index] ?? null;
	}
}