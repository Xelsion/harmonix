<?php

namespace models\entities;

use lib\core\attributes\PrimaryKey;

/**
 * The Token entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Token {
	#[PrimaryKey]
	public string $id = "";
	public string $expired = "";

}