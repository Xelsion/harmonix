<?php

namespace models;

use JsonException;
use system\classes\CacheFile;
use system\classes\PDOCache;
use system\classes\QueryBuilder;
use system\Core;
use system\exceptions\SystemException;
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
			$this->_role = new ActorRole( $this->role_id );
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
        $db = Core::$_connection_manager->getConnection("mvc");
        if( !is_null($db) ) {
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

            $db->prepare($query);
            foreach( $params as $key => $value ) {
                $db->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
            }

            $pdo_cache = new PDOCache($db);
            $pdo_cache->checkTable("mvc", "access_permissions");
            $results = $pdo_cache->getResults(__CLASS__);

        }

        return $results;
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
