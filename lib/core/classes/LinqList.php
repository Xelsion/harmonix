<?php

namespace lib\core\classes;

use Closure;
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

	private bool $selective = false;

	/**
	 * @throws SystemException
	 */
	public function __construct(array $data = []) {
		if( !empty($data) && !$this->isValidData($data) ) {
			throw new SystemException(__FILE__, __LINE__, "LinqList elements must be of the same type and structure");
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
			throw new SystemException(__FILE__, __LINE__, "LinqList elements must be of the same type and structure");
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
		$array = $this->iterator->getArrayCopy();
		$key = array_find_key($array, static fn($value) => valuesAreIdentical($entry, $value));
		if( $key !== null ) {
			$this->iterator->offsetUnset($key);
		}
	}

	/**
	 * Looks for elements in the list the matches the in conditions in the given function
	 *
	 * @param callable|null $callable $callable
	 * @return LinqList
	 */
	public function where(?callable $callable = null): self {
		$this->selective = true;
		$this->temp = [];
		if( $callable instanceof Closure ) {
			foreach( $this->iterator as $key => $entry ) {
				if( $callable($entry, $key, $this) ) {
					$this->temp[] = $entry;
				}
			}
		} else {
			$this->temp = $this->iterator->getArrayCopy();
		}
		return $this;
	}

	/**
	 * Applies the given function to each element of the results
	 *
	 * @param callable $callable
	 * @return $this
	 */
	public function select(callable $callable): self {
		$this->selective = true;
		if( !empty($this->temp) ) {
			foreach( $this->temp as $key => $value ) {
				$this->temp[$key] = $callable($value, $key, $this);
			}
		} else {
			foreach( $this->iterator as $key => $value ) {
				$this->temp[] = $callable($value, $key, $this);
			}
		}
		return $this;
	}


	/**
	 * Sorts the results by the given column if the values are arrays or objects in the given order direction
	 * or if the values are standard-types like string or int that will be sort in the given direction
	 *
	 * @param string $col
	 * @param bool $ascending
	 * @return $this
	 */
	public function orderBy(string $col = "", bool $ascending = true): self {
		if( !$this->selective && empty($this->temp) ) {
			$this->temp = $this->iterator->getArrayCopy();
		}
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
	 * Remove any duplicated entry
	 *
	 * @return $this
	 */
	public function distinct(): self {
		if( !$this->selective && empty($this->temp) ) {
			$this->temp = $this->iterator->getArrayCopy();
		}
		$this->temp = array_values(array_unique($this->temp, SORT_REGULAR));
		return $this;
	}

	/**
	 * Returns the number of matching elements
	 *
	 * @return int
	 */
	public function count(): int {
		if( !$this->selective && empty($this->temp) ) {
			$this->temp = $this->iterator->getArrayCopy();
		}
		return count($this->temp);
	}

	/**
	 * Returns all elements of the search result or the entire list if no selection was made
	 *
	 * @return array
	 */
	public function getAll(): array {
		if( !$this->selective && empty($this->temp) ) {
			$this->temp = $this->iterator->getArrayCopy();
		}
		$results = $this->temp;
		$this->temp = [];
		$this->selective = false;
		return $results;
	}

	/**
	 * Returns one element from the search results
	 * Throws an exception if more than one element is found
	 *
	 * @return mixed
	 * @throws SystemException
	 */
	public function getOneOrNull(): mixed {
		if( !$this->selective ) {
			throw new SystemException(__FILE__, __LINE__, "No selection was made!");
		}
		if( count($this->temp) > 1 ) {
			throw new SystemException(__FILE__, __LINE__, "Multiple values found!");
		}
		if( count($this->temp) === 1 ) {
			$result = $this->temp[array_key_first($this->temp)];
			$this->temp = [];
			$this->selective = false;
			return $result;
		}
		return null;
	}

	/**
	 * Returns the first element from the search results or from the entire list if no selection was made
	 *
	 * @return mixed
	 */
	public function getFirstOrNull(): mixed {
		if( !$this->selective && empty($this->temp) ) {
			$this->temp = $this->iterator->getArrayCopy();
		}
		if( count($this->temp) > 0 ) {
			$result = $this->temp[0];
			$this->temp = [];
			$this->selective = false;
			return $result;
		}
		return null;
	}

	/**
	 * Checks if the all elements in data have the same type or if they are an array of the same structure
	 *
	 * @param array $data
	 * @return bool
	 */
	private function isValidData(array $data): bool {
		if( $this->iterator->count() === 0 ) {
			return true;
		}
		$first_entry = $data[array_key_first($data)];
		return array_all($data, fn($entry) => $this->ofSameObjectType($first_entry, $entry));
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

		$array = $this->iterator->getArrayCopy();
		$first_entry = $array[array_key_first($array)];
		return $this->ofSameObjectType($first_entry, $value);
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
			if( count($keys1) !== count($keys2) || !empty(array_diff($keys1, $keys2)) ) {
				return false;
			}
			$values1 = array_values($entry1);
			$values2 = array_values($entry2);
			if( array_any($values1, static fn($value, $key) => getType($value) !== getType($values2[$key])) ) {
				return false;
			}
		}
		return true;
	}

}