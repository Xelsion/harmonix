<?php

namespace lib\core\blueprints;

use PDO;
use Exception;
use lib\App;
use lib\core\ConnectionManager;
use lib\core\database\PDOConnection;
use lib\core\exceptions\SystemException;
use lib\helper\MySqlHelper;

/**
 * The Abstract version of a Repository.
 * A Repository provides all function needed to communicate with the database.
 * Mostly each repository handles a single table but in some cases it could be more than
 * a single table.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class ARepository {

    protected PDOConnection $pdo;

    /**
     * @param string $table_name
     * @param string $class_name
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return array
     * @throws SystemException
     */
    public function findIn(string $table_name, string $class_name, array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1): array {
        try {
            $results = array();
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            if( !is_null($pdo) ) {
                $params = array();

                $query = "SELECT * FROM ". $table_name;
                if( !empty($conditions) ) {
                    $params = MySqlHelper::addQueryConditions($query, $conditions);
                }
                if( $order !== "" ) {
                    MySqlHelper::addQueryOrder($query, $order, $direction);
                }
                if( $limit > 0 ) {
                    $params = array_merge($params, MySqlHelper::addQueryLimit($query, $limit, $page));
                }

                $pdo->prepareQuery($query);
                foreach( $params as $key => $value ) {
                    $pdo->bindParam(":" . $key, $value, MySqlHelper::getParamType($value));
                }

                $pdo->setFetchMode(PDO::FETCH_CLASS, $class_name);
                $results = $pdo->execute()->fetchAll();
            }

            return $results;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}