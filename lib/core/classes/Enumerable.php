<?php

namespace lib\core\classes;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use lib\core\exceptions\SystemException;
use Traversable;

/**
 * The LinqList class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Enumerable implements IteratorAggregate {

	protected Traversable $iterator;

	protected int $index = -1;

	protected array $data = [];

	protected function __construct(array $data = []) {
		$this->iterator = new ArrayIterator($data);
	}

	/**
	 * @return Traversable
	 */
	public function getIterator(): Traversable {
		return $this->iterator;
	}

	/**
	 * Going to all elements and call a function on each element
	 *
	 * @param callable $callback
	 * @return void
	 * @throws SystemException
	 */
	public function each(callable $callback): void {
		$this->iterator->rewind();
		foreach( $this->iterator as $key => $value ) {
			try {
				$callback($value, $key, $this);
			} catch( Exception $e ) {
				throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage());
			}
		}
	}

	/**
	 * Returns the next element in the list
	 *
	 * @return mixed
	 */
	public function getNext(): mixed {
		if( $this->hasNext() ) {
			$this->iterator->next();
			return $this->iterator->current();
		}
		return false;
	}

	/**
	 * Checks if there are more elements in the list
	 *
	 * @return bool
	 */
	public function hasNext(): bool {
		return $this->iterator->valid();
	}

	/**
	 * Return if the given entry is in the list or not
	 *
	 * @param $entry
	 * @return bool
	 */
	public function contains($entry): bool {
		$index = $this->iterator->key();
		$this->iterator->rewind();
		$was_found = false;
		foreach( $this->iterator as $value ) {
			if( valuesAreIdentical($entry, $value) ) {
				$was_found = true;
				break;
			}
		}
		$this->iterator->seek($index);
		return $was_found;
	}

}
