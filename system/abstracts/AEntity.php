<?php

namespace system\abstracts;

use DateTime;
use JsonException;
use system\exceptions\SystemException;

/**
 * The Abstract version of an Entity
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AEntity {

    public array $data = array();

	/**
	 * Creates an entry in the database with the current object
	 * and tries to return the id of the new entry
	 *
	 * @throws JsonException
	 * @throws SystemException
	 */
	abstract public function create(): void;

    /**
     * Updates an entry in the database with the current object
     */
    abstract public function update(): void;

    /**
     * Deletes the current object from the database
     * Returns true if successful else false
     *
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
    abstract public function delete(): bool;

    /**
     * Converts a string to a DateTime object
     *
     * @param string $datetime
     * @return DateTime|false
     */
    public function str2DateTime( string $datetime ) {
        return DateTime::createFromFormat("Y-m-d H:i:s", $datetime);
    }

    public function __set(string $key, mixed $value): void {
        $this->data[$key] = $value;
    }

    public function __get(string $key): mixed {
        return $this->data[$key] ?? null;
    }

    public function __isset($key): bool {
        return isset($this->data[$key]);
    }

    public function __unset($key): void {
        if( array_key_exists($key, $this->data) ) {
            unset($this->data[$key]);
        }
    }

}
