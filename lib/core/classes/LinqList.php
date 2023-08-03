<?php

namespace lib\core\classes;

use Closure;
use Exception;
use lib\core\exceptions\SystemException;
use lib\helper\DataHelper;

/**
 * The LinqList class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class LinqList extends Enumerable {

	private array $temp = [];

	/**
	 * @throws Exception
	 */
	public function __construct(array $data = []) {
		if( !empty($data) && !$this->isValidData($data) ) {
			throw new SystemException(__FILE__, __LINE__, "LinqList elements must be of the same type", debug_backtrace());
		}
		parent::__construct($data);
	}

	/**
	 * Adds an element to the list
	 *
	 * @param mixed $value
	 * @return void
	 * @throws SystemException
	 */
	public function add(mixed $value): void {
		if( !$this->isValidValue($value) ) {
			throw new SystemException(__FILE__, __LINE__, "LinqList elements must be of the same type", debug_backtrace());
		}
		$this->iterator->append($value);
	}

	/**
	 * Removes an element from the list
	 *
	 * @param mixed $entry
	 * @return void
	 */
	public function remove(mixed $entry): void {
		$this->iterator->rewind();
		foreach( $this->iterator as $key => $value ) {
			if( valuesAreIdentical($entry, $value) ) {
				$this->iterator->offsetUnset($key);
				break;
			}
		}
	}

	/**
	 * Looks for elements in the list the matches the in conditions in the given function
	 *
	 * @param callable|null $callable $callable
	 * @return LinqList
	 */
	public function where(callable $callable = null): LinqList {
		$this->iterator->rewind();
		$this->temp = [];
		if( $callable instanceof Closure ) {
			foreach( $this->iterator as $key => $entry ) {
				$return_value = $callable($entry, $key, $this);
				if( $return_value ) {
					$this->temp[] = $return_value;
				}
			}
		} else {
			$this->temp = $this->iterator->getArrayCopy();
		}
		return $this;
	}

	/**
	 * Sorts the results by the given column if the values are arrays or objects in the given order direction
	 * or if the values are standard-types like string or int the will be sort in the given direction
	 *
	 * @param string $col
	 * @param bool $ascending
	 * @return $this
	 */
	public function orderBy(string $col = "", bool $ascending = true): LinqList {
		if( !empty($this->temp) ) {
			if( $col !== "" ) {
				$first_element = $this->temp[array_key_first($this->temp)];
				if( is_object($first_element) ) {
					usort($this->temp, static function($a, $b) use ($col, $ascending) {
						$type = getType($a->$col);
						return match (true) {
							$type === "integer" || $type === "double" => DataHelper::numberCompare($a->$col, $b->$col, $ascending),
							$type === "string" => DataHelper::stringCompare($a->$col, $b->$col, $ascending),
							default => 0,
						};
					});
				} else if( is_array($first_element) ) {
					usort($this->temp, static function($a, $b) use ($col, $ascending) {
						$type = getType($a[$col]);
						return match (true) {
							$type === "integer" || $type === "double" => DataHelper::numberCompare($a[$col], $b[$col], $ascending),
							$type === "string" => DataHelper::stringCompare($a[$col], $b[$col], $ascending),
							default => 0,
						};
					});
				}
			} else {
				usort($this->temp, static function($a, $b) use ($ascending) {
					$type = getType($a);
					return match (true) {
						$type === "integer" || $type === "double" => DataHelper::numberCompare($a, $b, $ascending),
						$type === "string" => DataHelper::stringCompare($a, $b, $ascending),
						default => 0,
					};
				});
			}
		}
		return $this;
	}

	/**
	 * Returns one element rom the search results
	 * Throws an exception if more than one element is found
	 *
	 * @return mixed
	 * @throws SystemException
	 */
	public function getOne(): mixed {
		if( count($this->temp) === 1 ) {
			$result = $this->temp[array_key_first($this->temp)];
			$this->temp = [];
			return $result;
		}
		if( count($this->temp) > 1 ) {
			throw new SystemException(__FILE__, __LINE__, "Multiple values found!", debug_backtrace());
		}
		return null;
	}

	/**
	 * Returns the first element rom the search results
	 *
	 * @return mixed
	 */
	public function getFirst(): mixed {
		if( count($this->temp) > 0 ) {
			$result = $this->temp[0];
			$this->temp = [];
			return $result;
		}
		return null;
	}

	/**
	 * Returns all elements of the search result
	 *
	 * @return array
	 */
	public function getAll(): array {
		$results = $this->temp;
		$this->temp = [];
		return $results;
	}

	/**
	 * Checks if the all elements in data have the same type or if they are array the same structure
	 *
	 * @param array $data
	 * @return bool
	 */
	private function isValidData(array $data): bool {
		$first_entry = $data[0];
		foreach( $data as $entry ) {
			if( !$this->ofSameObjectType($first_entry, $entry) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks if the given element shares the same type as the rest of the elements in the list
	 *
	 * @param mixed $value
	 * @return bool
	 */
	private function isValidValue(mixed $value): bool {
		if( $this->iterator->count() === 0 ) {
			return true;
		}
		$first_entry = $this->iterator->current();
		if( !$this->ofSameObjectType($first_entry, $value) ) {
			return false;
		}
		return true;
	}

	/**
	 * compares two values to the same type and structure
	 *
	 * @param $entry1
	 * @param $entry2
	 * @return bool
	 */
	private function ofSameObjectType($entry1, $entry2): bool {
		$type1 = getType($entry1);
		if( $type1 !== getType($entry2) ) {
			return false;
		}
		if( $type1 === "object" && get_class($entry1) !== get_class($entry2) ) {
			return false;
		}
		if( $type1 === "array" ) {
			$keys1 = array_keys($entry1);
			$keys2 = array_keys($entry2);
			if( !empty(array_diff($keys1, $keys2)) ) {
				return false;
			}
			$values1 = array_values($entry1);
			$values2 = array_values($entry2);
			foreach( $values1 as $key => $value ) {
				if( getType($value) !== getType($values2[$key]) ) {
					return false;
				}
			}
		}
		return true;
	}

}