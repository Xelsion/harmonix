<?php

namespace lib\core\resolver;

use lib\core\ClassManager;
use lib\core\exceptions\SystemException;
use ReflectionClass;
use ReflectionException;

/**
 * Tries to getInstanceOf an Instance of the given namespace.
 * checks the class behind the namespace and if there are any dependencies required to create
 * and instance of it. If there are dependencies it uses the ClassManager to resolve them recursively.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ClassResolver {

	protected ClassManager $class_manager;
	protected string $namespace;
	protected array $args;

	// stack for dependencies (circular check)
	protected static array $resolving = [];

	/**
	 * The class constructor
	 *
	 * @param ClassManager $class_manager
	 * @param string $namespace
	 * @param array $args
	 */
	public function __construct(ClassManager $class_manager, string $namespace, array $args = []) {
		$this->class_manager = $class_manager;
		$this->namespace = $namespace;
		$this->args = $args;
	}

	/**
	 * Tries to build an instance of the current namespace nad returns it.
	 *
	 * @return object
	 *
	 * @throws ReflectionException
	 * @throws SystemException
	 */
	public function getInstance(): object {
		// Check for Singletons/Bindings
		if( $this->class_manager->has($this->namespace) ) {
			$binding = $this->class_manager->get($this->namespace);
			if( is_object($binding) ) {
				return $binding;
			}
			$this->namespace = $binding;
		}

		// check for circular dependency
		if( isset(self::$resolving[$this->namespace]) ) {
			throw new SystemException(__FILE__, __LINE__, "Circular dependency detected while resolving: " . $this->namespace);
		}
		self::$resolving[$this->namespace] = true;

		try {
			$refClass = new ReflectionClass($this->namespace);

			// Is it instantiable?
			if( !$refClass->isInstantiable() ) {
				throw new SystemException(__FILE__, __LINE__, "Class {$this->namespace} is not instantiable (Abstract or Private Constructor).");
			}

			// Check the constructor
			$constructor = $refClass->getConstructor();
			if( $constructor && $constructor->isPublic() ) {
				$params = $constructor->getParameters();

				if( count($params) > 0 ) {
					$argumentResolver = new ParameterResolver($this->class_manager, $params, $this->args);
					return $refClass->newInstanceArgs($argumentResolver->getArguments());
				}
				return $refClass->newInstance();
			}

			// Return instance without constructor
			return $refClass->newInstanceWithoutConstructor();
		} finally {
			// clear stack
			unset(self::$resolving[$this->namespace]);
		}
	}

}