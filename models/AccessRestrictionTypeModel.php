<?php
namespace models;

use PDO;
use lib\App;
use lib\helper\SqlHelper;
use lib\manager\ConnectionManager;

use Exception;
use lib\exceptions\SystemException;

/**
 * The AccessRestrictionTypeModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessRestrictionTypeModel extends entities\AccessRestrictionType {

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
     *
     * @return array|null
     *
     * @throws SystemException
     */
    public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : ?array {
        try {
            $results = array();
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            if( !is_null($pdo) ) {
                $params = array();

                $query = "SELECT * FROM access_restriction_types";
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
                $pdo->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
                $results = $pdo->execute()->fetchAll();
            }

            return $results;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}
