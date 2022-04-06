<?php

namespace models;

use DateTime;
use system\Core;
use PDO;
use system\helper\SqlHelper;

/**
 * The Actor Permissions
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessPermission extends entities\AccessPermission {

	private ?ActorRole $_role = null;

	/**
	 * The class constructor
	 * If id is 0 it will return an empty actor
	 *
	 * @param int $id
	 */
	public function __construct() {
        parent::__construct();
		if( $this->role_id > 0 ) {
			$this->_role = new ActorRole($this->role_id);
		}
	}

    /**
     * Returns all actors permissions that mach the given conditions,
     * The condition array is build like this:
     * <p>
     * array {
     *    array { col, condition, value },
     *    ...
     * }
     * </p>
     * All conditions are AND related
     *
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return array|false|null
     */
	public static function find( array $conditions, ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : ?array {
        $pdo = SqlHelper::findIn("mvc", "access_permissions", $conditions, $order, $direction, $limit, $page);
        return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

    /**
     * Returns all actors
     * If limit is greater than 0 the query will return
     * that many results starting at index.
     * Returns false if an error occurs
     *
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return array|false
     */
    public static function findAll( ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): ?array {
        $pdo = SqlHelper::findAllIn("mvc", "access_permissions", $order, $direction, $limit, $page);
        return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getLastModification() : int {
        $created = 0;
        $updated = 0;
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $pdo->prepare("SELECT max(created) as created, max(updated) as updated FROM access_permissions LIMIT 1");
        $row = $pdo->execute()->fetch();
        if( $row ) {
            $created = new DateTime($row["created"]);
            $created = $created->getTimestamp();
            $updated = new DateTime($row["updated"]);
            $updated = $updated->getTimestamp();
        }
        return ( $created >= $updated ) ? $created : $updated;
    }

	/**
	 * Returns the role of this permission
	 *
	 * @return ActorRole|null
	 */
	public function getRole(): ?ActorRole {
		return $this->_role;
	}
}