<?php
namespace models\entities;

/**
 * The AccessPermissionModel entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessPermission {

	// The columns
	public int $actor_id = 0;

	public int $role_id = 0;

	public string $domain = "";

	public ?string $controller = null;

	public ?string $method = null;

    public string $created = "";

    public ?string $updated = null;

    public ?string $deleted = null;

}
