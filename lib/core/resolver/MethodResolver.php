<?php

namespace lib\core\resolver;

use lib\core\ClassManager;
use lib\core\exceptions\SystemException;
use ReflectionException;
use ReflectionMethod;

/**
 * Tries to run of the given method of the given instance.
 * Checks the method needs any parameters and tries to solve them.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class MethodResolver {

	protected ClassManager $class_manager;
	protected object $instance;
	protected string $method;
	protected array $args;

	/**
	 * The class constructor
	 *
	 * @param ClassManager $class_manager
	 * @param object $instance
	 * @param string $method
	 * @param array $args
	 */
	public function __construct(ClassManager $class_manager, object $instance, string $method, array $args = []) {
		$this->class_manager = $class_manager;
		$this->instance = $instance;
		$this->method = $method;
		$this->args = $args;
	}

	/**
	 * Calls the current method and returns its result
	 *
	 * @return mixed
	 *
	 * @throws ReflectionException
	 * @throws SystemException
	 */
	public function getValue(): mixed {
		// getInstanceOf the class method reflection class
		$method = new ReflectionMethod($this->instance, $this->method);
		// find and resolve the method arguments
		$argumentResolver = new ParameterResolver($this->class_manager, $method->getParameters(), $this->args);
		// call the method with the injected arguments
		return $method->invokeArgs($this->instance, $argumentResolver->getArguments());
	}
}