<?php
namespace lib\core;

/**
 * The Storage class
 * A storage for the whole project in key => value pairs
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Storage {

    // The storage as array
    private static array $storage = array();

    /**
     * Set a key value pair into the storage
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public static function set($key, $value): void {
        static::$storage[$key] = $value;
    }

    /**
     * Returns the value of the given key if setClass or null if not
     *
     * @param $key
     *
     * @return mixed|null
     */
    public static function get($key): mixed {
        return static::$storage[$key] ?? null;
    }

    /**
     * Add a value to an array within the storage
     *
     * @param $array_name
     * @param $value
     *
     * @return void
     */
    public static function add($array_name, $value): void {
        static::$storage[$array_name][] = $value;
    }

    /**
     * Returns if the given key exists in the storage
     *
     * @param $key
     *
     * @return bool
     */
    public static function contains( $key ): bool {
        return ( array_key_exists($key, self::$storage) );
    }

}
