<?php

namespace models;

use JsonException;
use PDO;

use system\classes\Cache;
use system\classes\QueryBuilder;
use system\Core;
use system\exceptions\SystemException;
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
     *
     * @throws JsonException
     * @throws SystemException
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
     *
     * @throws JsonException
     * @throws SystemException
     */
	public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): array {
        $queryBuilder = new QueryBuilder("mvc");
        $queryBuilder->setTable("actors");
        if( !empty($conditions) ) {
            $queryBuilder->setConditions( $conditions );
        }
        if( !is_null($order) && $order !== "" ) {
            $queryBuilder->setOrder($order, $direction);
        }
        if( $limit > 0 ) {
            $queryBuilder->setLimit($limit, $page);
        }
        $queryBuilder->setFetchClass(__CLASS__);

        if( self::isCacheable() ) {
            $cache = new Cache(md5($queryBuilder->getCacheName()));
            $last_modify = $queryBuilder->getLastModificationDate();
            if( $cache->isUpToDate($last_modify) ) {
                $results = unserialize($cache->loadFromCache(), array(false));
            } else {
                $results = $queryBuilder->getResults();
                $cache->saveToCache(serialize($results));
            }
        } else {
            $results = $queryBuilder->getResults();
        }

        return $results;
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
     * @return ActorRole
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function getRole( string $controller, string $method, $domain = SUB_DOMAIN ): ActorRole {
        // do we have a loaded actor object?
        if( $this->id > 0 ) {
            // no permission restriction is set?
			if( empty($this->_permissions) ) {
				$this->initPermission();
			}

            // check if there is a permission set for this method if so return the actor role
			if( isset($this->_permissions[$domain][$controller][$method]) ) {
				return $this->_permissions[$domain][$controller][$method];
			}

            // check if there is a permission set for this controller if so return the actor role
			if( isset($this->_permissions[$domain][$controller][null]) ) {
				return $this->_permissions[$domain][$controller][null];
			}

            // check if there is a permission set for this domain if so return the actor role
			if( isset($this->_permissions[$domain][null][null]) ) {
				return $this->_permissions[$domain][null][null];
			}
		}

        // actor object is not loaded, so we return the default actor role
		$result = ActorRole::find(array(
			array( "is_default", "=", 1 )
		));
        if( count($result) === 1 ) {
            return $result[0];
        }

        // if no default actor role could be found return an empty actor role
		return new ActorRole();
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
		$permissions = AccessPermission::find(array(["actor_id", "=", $this->id] ));
		foreach( $permissions as $permission ) {
			$this->_permissions[$permission->domain][$permission->controller][$permission->method] = $permission->getRole();
		}
	}

}