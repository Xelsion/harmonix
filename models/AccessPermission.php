<?php

namespace models;

use Exception;
use JsonException;
use system\exceptions\SystemException;

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
     * @throws JsonException
     * @throws SystemException
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
     *
     * @throws JsonException
     * @throws SystemException
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
     * @throws JsonException
     * @throws SystemException
     */
    public static function findAll( ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): ?array {
        $pdo = SqlHelper::findAllIn("mvc", "access_permissions", $order, $direction, $limit, $page);
        return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
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