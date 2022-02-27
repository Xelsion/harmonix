<?php

namespace core\interfaces;

interface IModel {

	/**
	 * If the current primary key id not set creates a new
	 * entry in the database else it updates the current entry
	 * with the current object
	 */
	public function save(): void;

	/**
	 * Deletes the current object from the database
	 * Returns true if successful else false
	 *
	 * @return bool
	 */
	public function delete(): bool;

}