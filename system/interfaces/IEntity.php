<?php

namespace system\interfaces;

/**
 * The Entity interface
 * Defines necessary methods for all entities
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
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