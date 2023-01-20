<?php

namespace lib\core;

use lib\core\resolver\ClassResolver;
use lib\core\resolver\MethodResolver;
use lib\exceptions\SystemException;
use ReflectionException;

/**
 * The class manager is a Dependency Injection container.
 * Provides an instance of any class with its dependencies, also these
 * dependencies can be injected.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ClassManager {

    // Stores classes under the specified namespace.
    private array $entries = array();

    /**
     * Sets the given namespace to the given class
     *
     * @param string $namespace
     * @param callable|string $concrete
     *
     * @return void
     */
    public function set(string $namespace, callable|string $concrete ): void {
        $this->entries[$namespace] = $concrete;
    }

    /**
     * Sets the given namespace to the given instance
     *
     * @param string $namespace
     * @param object $instance
     *
     * @return void
     */
    public function sigleton(string $namespace, object $instance ): void {
        $this->entries[$namespace] = $instance;
    }

    /**
     * Returns an instance for the given namespace
     *
     * @param string $namespace
     * @param string $method
     * @param array $args
     *
     * @return mixed
     *
     * @throws ReflectionException
     * @throws SystemException
     */
    public function get(string $namespace, string $method = "", array $args = [] ): mixed {
        if( $this->has($namespace) ) {
            $entry =$this->entries[$namespace];
            if( is_callable($entry) ) {
                return $entry($this);
            } elseif( is_object($entry) ) {
                return $entry;
            }
            $namespace = $entry;
        }

        if( $method !== "") {
            return (new MethodResolver($this, $namespace, $method, $args))->getValue();
        }

        return (new ClassResolver($this, $namespace, $args))->getInstance();
    }

    /**
     * Checks if the namespace is already registered
     *
     * @param string $namespace
     *
     * @return bool
     */
    public function has( string $namespace ): bool {
        return (isset($this->entries[$namespace]));
    }

}