<?php
namespace models\entities;

/**
 * The AccessRestrictionType entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessRestrictionType {

    public int $id = 0;

    public string $name = "";

    public int $include_siblings = 0;

    public int $include_children = 0;

    public int $include_descendants = 0;

    public string $created = "";

    public ?string $updated = null;

    public ?string $deleted = null;

}
