<?php
namespace system;

use JetBrains\PhpStorm\Pure;

/**
 * The system class holds all important object
 * accessible from anywhere
 *
 * @property mixed $menu
 * @property mixed $router
 * @property mixed $auth
 * @property mixed $configuration
 * @property mixed $debugger
 * @property mixed $connection_manager
 * @property mixed $request
 * @property mixed $lang
 * @property mixed $analyser
 * @property mixed $role_tree
 * @property mixed $actor
 * @property mixed $actor_role
 * @property mixed $response_cache
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Core extends Storage {

    private static ?Core $instance = null;
    private static array $classes = array();

    /**
     * The class constructor
     * will be called once by the static method getInstance()
     */
    private function __construct() {

    }

    /**
     * The initializer for this class
     *
     * @return Core
     */
    public static function getInstance(): Core {
        if( is_null(static::$instance) ) {
            static::$instance = new Core();
        }
        return static::$instance;
    }

    /**
     * The magic method will set a parameter with the given name (key)
     * and initialize it with the given value
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function __set($key, $value): void {
        if( !isset(self::$classes[$key]) ) {
            self::$classes[$key] = $value;
        }
    }

    /**
     * The magic method __get will return the value of the parameter with the name (key)
     * or null if it's not set
     *
     * @param $key
     *
     * @return mixed
     */
    public function __get($key): mixed {
        return self::$classes[$key] ?? null;
    }

    /**
     * The magic method __isset will check if the parameter with the given name (key)
     * is set og not
     *
     * @param $key
     *
     * @return bool
     */
    public function __isset($key): bool {
        return isset(self::$classes[$key]);
    }

    /**
     * The magic method __unset will unset the parameter with the given name (key)
     *
     * @param $key
     *
     * @return void
     */
    public function __unset($key): void {
        if( array_key_exists($key, self::$classes) ) {
            unset(self::$classes[$key]);
        }
    }

}
