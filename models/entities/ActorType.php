<?php

namespace models\entities;

use lib\core\attributes\PrimaryKey;

/**
 * The ActorType entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorType {
	#[PrimaryKey]
	public int $id = 0;

	public string $name = "";

	public bool $is_protected = false;

	public string $created = "";

	public ?string $updated = null;

	public ?string $deleted = null;

}
