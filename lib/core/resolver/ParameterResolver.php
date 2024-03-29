<?php

namespace lib\core\resolver;

use lib\core\ClassManager;
use lib\core\exceptions\SystemException;
use ReflectionException;
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
			// if an additional arg that was passed in return that value
			if( array_key_exists($param->getName(), $this->args) ) {
				return $this->args[$param->getName()];
			}
			// if the parameter is a class, resolve it and return it
			// otherwise return the default value
			return ($param->getType() && !$param->getType()->isBuiltin())
				? $this->getClassInstance($param->getType()->getName())
				: $param->getDefaultValue();
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
		return (new ClassResolver($this->class_manager, $namespace))->getInstance();
	}
}