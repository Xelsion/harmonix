<?php

namespace models\entities;

use lib\core\attributes\PrimaryKey;

/**
 * The ActorRole entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorRole {
	#[PrimaryKey]
	public int $id = 0;
	public ?int $child_of = null;
	public string $name = "";
	public int $rights_all = 0b0000;
	public int $rights_group = 0b0000;
	public int $rights_own = 0b0000;
	public bool $is_default = false;
	public bool $is_protected = false;
	public string $created = "";
	public ?string $updated = null;
	public ?string $deleted = null;
}
