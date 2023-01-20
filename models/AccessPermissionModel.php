<?php

namespace models;

use JsonException;
use lib\core\System;
use lib\exceptions\SystemException;
use lib\helper\SqlHelper;
use PDO;

/**
 * The ActorModel Permissions
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessPermissionModel extends entities\AccessPermission {

	private ?ActorRoleModel $role = null;

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
			$this->role = new ActorRoleModel( $this->role_id );
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
	 * @return array
	 *
	 * @throws JsonException
	 * @throws SystemException
	 */
	public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): array {
        $results = array();
        $pdo = System::$Core->connection_manager->getConnection("mvc");
        if( !is_null($pdo) ) {
            $params = array();

            $query = "SELECT * FROM access_permissions";
            if( !empty($conditions) ) {
                $columns = array();

                foreach( $conditions as $i => $condition ) {
                    $columns[] = $condition[0] . $condition[1] . ":" . $i;
                    $params[$i] = $condition[2];
                }
                $query .= " WHERE " . implode(" AND ", $columns);
            }

            if( $order !== "" ) {
                $query .= " ORDER BY " . $order . " " . $direction;
            }

            if( $limit > 0 ) {
                $offset = $limit * ($page - 1);
                $query .= " LIMIT :limit OFFSET :offset";
                $params["limit"] = $limit;
                $params["offset"] = $offset;
            }

            $pdo->prepareQuery($query);
            foreach( $params as $key => $value ) {
                $pdo->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
            }
            $pdo->setFetchMode(PDO::FETCH_CLASS, __CLASS__ );
            $results = $pdo->execute()->fetchAll();
        }
        return $results;
	}

	/**
	 * Returns the role of this permission
	 *
	 * @return ActorRoleModel|null
	 */
	public function getRole(): ?ActorRoleModel {
		return $this->role;
	}
}
