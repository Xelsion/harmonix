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
		// Sicherstellen, dass die Methode existiert, bevor Reflection angeworfen wird
		if( !method_exists($this->instance, $this->method) ) {
			throw new SystemException(__FILE__, __LINE__, sprintf("Method %s::%s() does not exist.", $this->instance::class, $this->method));
		}

		$refMethod = new ReflectionMethod($this->instance, $this->method);

		// Sicherheitscheck: Nur öffentliche Methoden erlauben (oder explizit zugänglich machen)
		if( !$refMethod->isPublic() ) {
			throw new SystemException(__FILE__, __LINE__, sprintf("Method %s::%s() is not public and cannot be resolved via DI.", $this->instance::class, $this->method));
		}

		// Nutzt den verbesserten ParameterResolver für die Methoden-Argumente
		$argumentResolver = new ParameterResolver($this->class_manager, $refMethod->getParameters(), $this->args);

		// Aufruf mit den aufgelösten Argumenten
		return $refMethod->invokeArgs($this->instance, $argumentResolver->getArguments());
	}
}