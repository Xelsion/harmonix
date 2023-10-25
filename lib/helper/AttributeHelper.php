<?php

namespace lib\helper;

use lib\core\attributes\PrimaryKey;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;

/**
 * The AttributeHelper class
 * will help to read an available attributes in classes
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
readonly class AttributeHelper {

	/**
	 * Try to get the primary keys of an entity and returns them
	 *
	 * @param string $entity
	 * @return array
	 */
	public static function getPrimaryKeysOfEntity(string $entity): array {
		$primary_keys = [];
		$reflection = new ReflectionObject(new $entity());
		$properties = $reflection->getProperties();
		foreach( $properties as $property ) {
			$attributes = $property->getAttributes(PrimaryKey::class);
			if( !empty($attributes) ) {
				$primary_keys[] = [$property->getType()?->getName(), $property->getName()];
			}
		}
		return $primary_keys;
	}

	/**
	 * Gets the Attribute defined Route in a controller and returns them
	 *
	 * @param AController $controller
	 * @return array
	 * @throws ReflectionException
	 */
	public static function getControllerRoutes(AController $controller): array {
		$results = array();
		$reflection = new ReflectionClass($controller::class);
		$class_attributes = $reflection->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);
		$class_path = "";

		if( !empty($class_attributes) ) {
			foreach( $class_attributes as $attr ) {
				$class_route = $attr->newInstance();
				$class_path = $class_route->path;
			}
		}

		foreach( $reflection->getMethods() as $method ) {
			$method_attributes = $method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);
			if( !empty($method_attributes) ) {
				foreach( $method_attributes as $attr ) {
					$route = $attr->newInstance();
					$method_path = $route->path;
					$route->path = self::getClearedRoutePath($class_path . "/" . $method_path);
					$results[] = [
						"route"      => $route,
						"controller" => $controller::class,
						"method"     => $method->getName()
					];
				}
			}
		}
		return $results;
	}

	/**
	 * formats the given path string to a valid route string and returns the cleared string
	 * Removes any unwanted characters like "//" or ending slashes or Add a starting slash if non exists
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function getClearedRoutePath(string $path): string {
		$route_path = preg_replace("/\/{2,}/", "/", $path);
		if( !str_starts_with($route_path, "/") ) {
			$route_path = "/" . $route_path;
		}
		if( $route_path !== "/" && str_ends_with($route_path, "/") ) {
			$route_path = substr($route_path, 0, -1);
		}
		return $route_path;
	}

}