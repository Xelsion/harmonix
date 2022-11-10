<?php

namespace system\classes;

/**
 * The Storage class
 * A storage for the whole project in key => value pairs
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Storage {

    private static array $storage = array();

    public static function set($key, $value): void {
        static::$storage[$key] = $value;
    }

    public static function get($key) {
        return static::$storage[$key] ?? null;
    }

    public static function add($array_name, $value): void {
        static::$storage[$array_name][] = $value;
    }

}
