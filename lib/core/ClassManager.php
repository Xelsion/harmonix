<?php

namespace lib\core;

use lib\core\exceptions\SystemException;
use lib\core\resolver\ClassResolver;
use lib\core\resolver\MethodResolver;
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
	public function set(string $namespace, callable|string $concrete): void {
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
	public function singleton(string $namespace, object $instance): void {
		$this->entries[$namespace] = $instance;
	}

	/**
	 * Returns an instance for the given namespace
	 * Given args contains to be key-value pairs and the keys must match the parameter names of the
	 * targets class constructor or methode.
	 *
	 * @param string $namespace
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 *
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function get(string $namespace, ?string $method = null, array $args = []): mixed {
		try {
			if( $this->has($namespace) ) {
				$entry = $this->entries[$namespace];
				if( is_callable($entry) ) {
					return $entry($this);
				}

				if( is_object($entry) ) {
					return $entry;
				}
				$namespace = $entry;
			}

			if( !is_null($method) && $method !== "" ) {
				return (new MethodResolver($this, $namespace, $method, $args))->getValue();
			}

			return (new ClassResolver($this, $namespace, $args))->getInstance();
		} catch( ReflectionException $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Checks if the namespace is already registered
	 *
	 * @param string $namespace
	 *
	 * @return bool
	 */
	public function has(string $namespace): bool {
		return (isset($this->entries[$namespace]));
	}

}