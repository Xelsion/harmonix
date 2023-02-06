<?php
namespace models;

use Exception;
use lib\App;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\helper\MySqlHelper;
use PDO;

/**
 * The ActorTypeModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorTypeModel extends entities\ActorTypes {

    /**
     * The class constructor
     * If id is 0 it will return an empty actor
     *
     * @param int $id
     *
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        parent::__construct($id);
    }

    /**
     * Returns all actor roles that mach the given conditions,
     * The condition array is build like this:
     * <p>
     * array {
     *    array { col, condition, value },
     *    ...
     * }
     * </p>
     * All conditions are AND related
     *
     * @param array $conditions default array()
     * @param string|null $order default ""
     * @param string|null $direction default ""
     * @param int $limit default 0
     * @param int $page default 1
     *
     * @return array
     *
     * @throws SystemException
     */
    public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : array {
        try {
            $results = array();
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            if( !is_null($pdo) ) {
                $params = array();

                $query = "SELECT * FROM actor_types";
                if( !empty($conditions) ) {
                    $params = MySqlHelper::addQueryConditions( $query, $conditions);
                }
                if( $order !== "" ) {
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


}
