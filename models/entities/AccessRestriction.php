<?php
namespace models\entities;

/**
 * The AccessRestriction entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessRestriction {

    public int $id = 0;

    public string $domain = "";

    public ?string $controller = null;

    public ?string $method = null;

    public int $restriction_type = 0;

    public int $role_id = 0;

    public string $created = "";

    public ?string $updated = null;

    public ?string $deleted = null;

}
