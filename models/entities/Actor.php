<?php

namespace models\entities;

use lib\core\attributes\PrimaryKey;
use lib\core\enums\ActorType;

/**
 * The ActorModel entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Actor {
	#[PrimaryKey]
	public int $id = 0;

	public int $type_id = ActorType::User->value;

	public string $email = "";

	public string $password = "";

	public string $first_name = "";

	public string $last_name = "";

	public int $login_fails = 0;

	public bool $login_disabled = false;

	public string $created = "";

	public ?string $updated = null;

	public ?string $deleted = null;

}
