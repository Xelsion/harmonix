<?php
namespace models\entities;

/**
 * The SessionModel entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Session {

	// The columns of the DB table
    public string $id = "";

    public int $actor_id = 0;
    public int $as_actor = 0;

    public string $ip = "";

    public string $expired = "";

    public string $created = "";

    public ?string $updated = null;

}
