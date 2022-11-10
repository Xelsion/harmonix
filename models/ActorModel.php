<?php

namespace models;

use PDO;
use JsonException;

use system\Core;
use system\helper\SqlHelper;
use system\exceptions\SystemException;

/**
 * The ActorModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorModel extends entities\Actor {

	// a collection of all permission this user has
	public array $permissions = array();

    /**
     * The class constructor
     * If id is 0 it will return an empty actor
     *
     * @param int $id
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function __construct( int $id = 0 ) {
		parent::__construct($id);
        if( $id > 0 ) {
            $this->initPermission();
        }
	}

    /**
     * Returns all actors that mach the given conditions,
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
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
	public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): array {
        $results = array();
        $pdo = Core::$_connection_manager->getConnection("mvc");
        if( !is_null($pdo) ) {
            $params = array();

            $query = "SELECT * FROM actors";
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

            $pdo->prepare($query);
            foreach( $params as $key => $value ) {
                $pdo->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
            }
            $pdo->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
            $results = $pdo->execute()->fetchAll();
        }

        return $results;
	}

    /**
     * Returns the number of total actors
     *
     * @return int
     */
    public static function getNumActors(): int {
        return Core::$_connection_manager->getConnection("mvc")->getNumRowsOfTable("actors");
    }

    /**
     * Returns the actor role for the given controller method
     * If non is set for a specific method it will look for a
     * controller role and if non is set too, it will look for
     * the domain role
     *
     * @param string $controller
     * @param string $method
     * @param mixed|string $domain
     * @return ActorRoleModel
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function getRole( string $controller, string $method, $domain = SUB_DOMAIN ): ActorRoleModel {
        // do we have a loaded actor object?
        if( $this->id > 0 ) {
            // no permission restriction is set?
			if( empty($this->permissions) ) {
				$this->initPermission();
			}

            // check if there is a permission set for this method if so return the actor role
			if( isset($this->permissions[$domain][$controller][$method]) ) {
				return $this->permissions[$domain][$controller][$method];
			}

            // check if there is a permission set for this controller if so return the actor role
			if( isset($this->permissions[$domain][$controller][null]) ) {
				return $this->permissions[$domain][$controller][null];
			}

            // check if there is a permission set for this domain if so return the actor role
			if( isset($this->permissions[$domain][null][null]) ) {
				return $this->permissions[$domain][null][null];
			}
		}

        // actor object is not loaded, so we return the default actor role
		$result = ActorRoleModel::find(array(
			array( "is_default", "=", 1 )
		));
        if( count($result) === 1 ) {
            return $result[0];
        }

        // if no default actor role could be found return an empty actor role
		return new ActorRoleModel();
	}

    /**
     * @return bool
     *
     * @throws SystemException
     * @throws JsonException
     */
    public function deletePermissions() : bool {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        if( $this->id > 0 ) {
            $pdo->prepare("DELETE FROM access_permissions WHERE actor_id=:actor_id");
            $pdo->bindParam(':actor_id', $this->id, PDO::PARAM_INT);
            $pdo->execute();
            return true;
        }
        return false;
    }


	/**
	 * Collects all permission for this user
     *
     * @throws SystemException
     * @throws JsonException
	 */
	private function initPermission(): void {
		$permissions = AccessPermissionModel::find(array(["actor_id", "=", $this->id] ));
		foreach( $permissions as $permission ) {
			$this->permissions[$permission->domain][$permission->controller][$permission->method] = $permission->getRole();
		}
	}

}
