<?php

namespace core\interfaces;

interface IEntity {

	/**
	 * Creates an entry in the database with the current object
	 * and tries to return the id of the new entry
	 */
	public function create();

	/**
	 * Updates an entry in the database with the current object
	 */
	public function update(): void;

	/**
	 * Deletes the current object from the database
	 * Returns true if successful else false
	 *
	 * @return bool
	 */
	public function delete(): bool;

}