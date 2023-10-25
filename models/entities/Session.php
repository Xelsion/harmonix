<?php

namespace models\entities;

use lib\core\attributes\PrimaryKey;

/**
 * The SessionModel entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Session {
	#[PrimaryKey]
	public string $id = "";

	public int $actor_id = 0;
	public int $as_actor = 0;

	public string $ip = "";

	public string $expired = "";

	public string $created = "";

	public ?string $updated = null;

}
