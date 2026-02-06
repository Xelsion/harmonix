<?php

namespace lib\core\cache;

/**
 * A cache class for Objects
 * The user can use this class to store Objects and reuse them at any point in the code
 */
class ObjectCache {

	private array $cached_objects;

	public function __construct() {
		$this->cached_objects = array();
	}

	/**
	 * @param string $class_name
	 * @param mixed $obj_key
	 * @param object $obj
	 * @return void
	 */
	public function set(string $class_name, mixed $obj_key, object $obj): void {
		$this->cached_objects[$class_name][$obj_key] = $obj;
	}

	/**
	 * @param string $class_name
	 * @param mixed $obj_key
	 * @return mixed
	 */
	public function get(string $class_name, mixed $obj_key): mixed {
		return $this->cached_objects[$class_name][$obj_key] ?? null;
	}
}