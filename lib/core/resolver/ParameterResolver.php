<?php

namespace lib\core\resolver;

use lib\core\ClassManager;
use lib\core\exceptions\SystemException;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * This class tries to create a valid value for each given parameter.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ParameterResolver {

	protected ClassManager $class_manager;
	protected array $parameters;
	protected array $args;

	/**
	 * The class constructor
	 *
	 * @param ClassManager $class_manager
	 * @param array $parameters
	 * @param array $args
	 */
	public function __construct(ClassManager $class_manager, array $parameters, array $args = []) {
		$this->class_manager = $class_manager;
		$this->parameters = $parameters;
		$this->args = $args;
	}

	/**
	 * Returns values for all parameters in an array
	 *
	 * @return array
	 *
	 * @throws ReflectionException
	 * @throws SystemException
	 */
	public function getArguments(): array {
		// loop through the parameters
		return array_map(function(ReflectionParameter $param) {
			$name = $param->getName();

			// check if named argument exists
			if( array_key_exists($name, $this->args) ) {
				return $this->args[$name];
			}

			$type = $param->getType();

			// Class initiation (Dependency Injection)
			// check if it is a named type (Union/Intersection ignored for autowiring)
			if( $type instanceof ReflectionNamedType && !$type->isBuiltin() ) {
				return $this->getClassInstance($type->getName());
			}

			// check for default values
			if( $param->isDefaultValueAvailable() ) {
				return $param->getDefaultValue();
			}

			// check for nullable values
			if( $param->allowsNull() ) {
				return null;
			}

			// param not resolvable
			throw new SystemException(__FILE__, __LINE__, sprintf("Unresolvable dependency [%s] in class %s", $name, $param->getDeclaringClass()
				?->getName() ?? 'unknown'));
		}, $this->parameters);
	}

	/**
	 * Returns an instance of a class
	 *
	 * @param string $namespace
	 *
	 * @return object
	 *
	 * @throws ReflectionException
	 * @throws SystemException
	 */
	protected function getClassInstance(string $namespace): object {
		return new ClassResolver($this->class_manager, $namespace)->getInstance();
	}
}