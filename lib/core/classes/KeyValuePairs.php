<?php

namespace lib\core\classes;

class KeyValuePairs {

    // an array for key => value pairs
    private array $entries = array();

    /**
     * Sets the value for the specified key.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, mixed $value): void {
        $this->entries[$key] = $value;
    }

    /**
     * Returns the value for the specified key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed {
        return $this->entries[$key] ?? null;
    }

    /**
     * Adds the value to an array the array $key at $index to the data storage
     *
     * @param string $key
     * @param mixed $value
     * @param mixed $index default null - if index is null it will be ignored
     */
    public function setToArray( string $key, mixed $value, mixed $index = null ): void {
        if( !is_null($index) ) {
            $this->entries[$key][$index] = $value;
        } else {
            $this->entries[$key][] = $value;
        }
    }

    /**
     * Returns the value from an array in the data storage by the given $key
     * and $index or null if the key was not found.
     *
     * @param string $key
     * @param mixed $index
     * @return mixed|null
     */
    public function getFromArray( string $key, mixed $index ): mixed {
        return $this->entries[$key][$index] ?? null;
    }

    /**
     * Returns if the specified key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function contains(string $key): bool {
        return isset($this->entries[$key]);
    }

    /**
     * Returns all existing keys
     *
     * @return array
     */
    public function getKeys(): array {
        return array_keys($this->entries);
    }

}