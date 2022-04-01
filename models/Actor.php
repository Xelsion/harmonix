<?php

namespace models;

use PDO;

use PDOException;
use RuntimeException;
use system\Core;
use system\helper\SqlHelper;

/**
 * The Actor
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Actor extends entities\Actor {

	// a collection of all permission this user has
	public array $_permissions = array();

	/**
	 * The class constructor
	 * If id is 0 it will return an empty actor
	 *
	 * @param int $id
	 */
	public function __construct( int $id = 0 ) {
		parent::__construct($id);
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
     * @return array|false|null
     */
	public static function find( array $conditions, ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): ?array {
		$pdo = SqlHelper::findIn("mvc", "actors", $conditions, $order, $direction, $limit, $page);
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
		$pdo = SqlHelper::findAllIn("mvc", "actors", $order, $direction, $limit, $page);
		return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

	/**
	 * Returns the actor role for the given controller method
	 * If non is set for a specific method it will look for a
	 * controller role and if non is set too, it will look for
	 * the domain role
	 *
	 * @param string $controller
	 * @param string $method
	 * @return ActorRole
	 */
	public function getRole( string $controller, string $method ): ActorRole {
		if( $this->id > 0 ) {
			if( empty($this->_permissions) ) {
				$this->initPermission();
			}

			if( isset($this->_permissions[SUB_DOMAIN][$controller][$method]) ) {
				return $this->_permissions[SUB_DOMAIN][$controller][$method];
			}

			if( isset($this->_permissions[SUB_DOMAIN][$controller][null]) ) {
				return $this->_permissions[SUB_DOMAIN][$controller][null];
			}

			if( isset($this->_permissions[SUB_DOMAIN][null][null]) ) {
				return $this->_permissions[SUB_DOMAIN][null][null];
			}
		}
		$result = ActorRole::find(array(
			array(
				"is_default",
				"=",
				1
			)
		));
        if( count($result) === 1 ) {
            return $result[0];
        }
		return new ActorRole();
	}

    public function deletePermissions() {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        if( $this->id > 0 ) {
            try {
                $pdo->prepare("DELETE FROM access_permissions WHERE actor_id=:actor_id");
                $pdo->bindParam(':actor_id', $this->id, PDO::PARAM_INT);
                $pdo->execute();
                return true;
            } catch( PDOException $e ) {
                throw new RuntimeException($e->getMessage());
            }
        }
    }


	/**
	 * Collects all permission for this user
	 */
	private function initPermission(): void {
		$permissions = AccessPermission::find(array(
			array(
				"actor_id",
				"=",
				$this->id
			)
		));

		foreach( $permissions as $permission ) {
			$this->_permissions[$permission->domain][$permission->controller][$permission->method] = $permission->getRole();
		}
	}

	public function toTableRow() {
		return "<div></div>";
	}

}