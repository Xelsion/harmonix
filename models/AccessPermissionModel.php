<?php
namespace models;

use Exception;
use lib\App;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\helper\MySqlHelper;
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
     * @throws SystemException
     */
	public function __construct() {
		parent::__construct();
		if( $this->role_id > 0 ) {
            try {
                $this->role = App::getInstanceOf(ActorRoleModel::class, null, ["id" => $this->role_id]);
            } catch( Exception $e ) {
                throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
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
     * @throws SystemException
     */
	public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): array {
        try {
            $results = array();
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            if( !is_null($pdo) ) {
                $params = array();

                $query = "SELECT * FROM access_permissions";
                if( !empty($conditions) ) {
                    $params = MySqlHelper::addQueryConditions( $query, $conditions);
                }
                if( !is_null($order) && $order !== "" ) {
                    MySqlHelper::addQueryOrder( $query, $order, $direction);
                }
                if( $limit > 0 ) {
                    $params = array_merge($params, MySqlHelper::addQueryLimit( $query, $limit, $page));
                }
                $pdo->prepareQuery($query);

                foreach( $params as $key => $value ) {
                    $pdo->bindParam(":" . $key, $value, MySqlHelper::getParamType($value));
                }
                $pdo->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
                $results = $pdo->execute()->fetchAll();
            }
            return $results;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
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
