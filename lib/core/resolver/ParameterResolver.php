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

			// 1. Priorität: Manuell übergebene Argumente (Named Arguments)
			if( array_key_exists($name, $this->args) ) {
				return $this->args[$name];
			}

			$type = $param->getType();

			// 2. Priorität: Klassen-Instanziierung (Dependency Injection)
			// Prüfen, ob es ein NamedType ist (Union/Intersection Types ignorieren wir für Autowiring)
			if( $type instanceof ReflectionNamedType && !$type->isBuiltin() ) {
				return $this->getClassInstance($type->getName());
			}

			// 3. Priorität: Default-Werte
			if( $param->isDefaultValueAvailable() ) {
				return $param->getDefaultValue();
			}

			// 4. Fallback: Nullable Types
			if( $param->allowsNull() ) {
				return null;
			}

			// 5. Error-Handling: Parameter nicht auflösbar
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