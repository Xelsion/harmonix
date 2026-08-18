<?php

namespace lib\core\classes;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use lib\core\exceptions\SystemException;
use Traversable;

/**
 * The Enumerable class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Enumerable implements IteratorAggregate {

	protected Traversable $iterator;

	protected int $index = -1;

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
	public function forEach(callable $callback): void {
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
	 * Return if the given entry is in the list or not
	 *
	 * @param $entry
	 * @return bool
	 */
	public function contains($entry): bool {
		$array = $this->iterator->getArrayCopy();
		return array_any($array, static fn($value) => valuesAreIdentical($entry, $value));
	}

	/**
	 * Return if the iterator is empty or not
	 *
	 * @return bool
	 */
	public function isEmpty(): bool {
		return ($this->iterator->count() === 0);
	}

}
