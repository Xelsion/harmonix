<?php

namespace lib\classes;

/**
 * This class can hold key => value pairs that
 * will be shared by all used templates
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TemplateData {

    // scripts and css that will be added to html head section
    private static array $header = array(
        "css" => array(),
        "script" => array()
    );

    // scripts that will be added to the end ob the html body section
    private static array $footer = array(
        "script" => array()
    );

	// the data storage for the template
	private static array $data = array();

	/**
	 * Adds a key => value pair to the data storage
	 *
	 * @param $key
	 * @param $value
	 */
	public function set( $key, $value ): void {
		static::$data[$key] = $value;
	}

	/**
	 * Returns the value vom the data storage by the given key
	 * or null if the key was not found.
	 *
	 * @param $key
	 * @return mixed|null
	 */
	public function get( $key ): mixed {
		return static::$data[$key] ?? null;
	}

    /**
     * Returns all data keys
     *
     * @return array
     */
    public function getKeys(): array {
        return array_keys(static::$data);
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
			static::$data[$name][$index] = $value;
		} else {
			static::$data[$name][] = $value;
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
	public function fromArray( $name, $index ): mixed {
		return static::$data[$name][$index] ?? null;
	}

    /**
     * Adds the given css to the headers css array
     *
     * @param string $value
     */
    public function addHeaderCss( string $value ): void {
        if( !in_array($value, static::$header["css"], false) ) {
            static::$header["css"][] = $value;
        }
    }

    /**
     * Returns all css added to the headers css array in an array
     *
     * @return array
     */
    public function getHeaderCss() : array {
        return static::$header["css"];
    }

    /**
     * Adds the given script to the headers script array
     *
     * @param string $value
     * @param bool $async
     */
    public function addHeaderScript( string $value, bool $async = false ): void {
        static::$header["script"][$value] = $async;
    }

    /**
     * Returns all scripts added to the headers script array in an array
     *
     * @return array
     */
    public function getHeaderScripts() : array {
        return static::$header["script"];
    }

    /**
     * Adds the given script to the footer script array
     *
     * @param string $value
     * @param bool $async
     */
    public function addFooterScript( string $value, bool $async = false ): void {
        static::$footer["script"][$value] = $async;
    }

    /**
     * Returns all scripts added to the footer script array in an array
     *
     * @return array
     */
    public function getFooterScripts() : array {
        return static::$footer["script"];
    }
}
