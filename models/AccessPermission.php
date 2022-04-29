<?php

namespace models;

use Exception;
use JsonException;
use MongoDB\Driver\Query;
use system\classes\Cache;
use system\classes\QueryBuilder;
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
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
	public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : array {
        $queryBuilder = new QueryBuilder("mvc");
        $queryBuilder->setTable("access_permissions");
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
	 * Returns the role of this permission
	 *
	 * @return ActorRole|null
	 */
	public function getRole(): ?ActorRole {
		return $this->_role;
	}
}