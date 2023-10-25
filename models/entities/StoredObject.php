<?php

namespace models\entities;

use lib\core\attributes\PrimaryKey;

class StoredObject {
	#[PrimaryKey]
	public int $id = 0;
	public int $actor_id = 0;
	public string $action = "";
	public string $connection_key = "";
	public string $table_name = "";
	public ?string $obj_before = null;
	public ?string $obj_after = null;
	public string $created = "";

}