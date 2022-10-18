<?php

namespace system\classes\cache;

use DateTime;
use PDO;
use system\classes\CacheFile;
use system\classes\PDOConnection;
use system\Core;

use Exception;
use JsonException;
use system\exceptions\SystemException;

/**
 * The PDOCache class
 * Can be used the store db results in a cache file
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class PDOCache {

    private PDOConnection $_pdo;
    private CacheFile $_cache;
    private int $_last_modified = 0;

    /**
     * The class constructor
     *
     * @param PDOConnection $conn
     */
    public function __construct( PDOConnection $conn ) {
        $this->_pdo = $conn;
        $cache_name = $this->_pdo->getFinalizedQuery();
        $this->_cache = new CacheFile($cache_name);
    }

    /**
     * @param string $db
     * @param string $table_name
     *
     * @return void
     *
     * @throws JsonException
     * @throws SystemException
     * @throws Exception
     */
    public function checkTable( string $db, string $table_name ): void {
        $modified = 0;
        $pdo = Core::$_connection_manager->getConnection($db, false);
        $pdo->prepare("SELECT create_time, update_time FROM information_schema.tables WHERE table_schema=:db AND table_name=:table");
        $pdo->bindParam("db", $db);
        $pdo->bindParam("table", $table_name);
        $row = $pdo->execute()->fetch();
        if( $row ) {
            if ( !is_null($row["update_time"]) ) {
                $last_update = new DateTime($row["update_time"]);
            } else {
                $last_update = new DateTime($row["create_time"]);
            }
            $modified = $last_update->getTimestamp();
        }
        $this->_last_modified = $modified;
    }

    /**
     * @param array $db_tables
     *
     * @return void
     *
     * @throws JsonException
     * @throws SystemException
     * @throws Exception
     */
    public function checkTables( array $db_tables ): void {
        $modified = 0;
        foreach( $db_tables as $db => $table_name  ) {
            $pdo = Core::$_connection_manager->getConnection($db, false);
            $pdo->prepare("SELECT update_time FROM information_schema.tables WHERE table_schema=:db AND table_name=:table");
            $pdo->bindParam("db", $db);
            $pdo->bindParam("table", $table_name);
            $row = $pdo->execute()->fetch();
            if( $row ) {
                $last_update = new DateTime($row["update_time"]);
                if( $modified < $last_update->getTimestamp() ) {
                    $modified = $last_update->getTimestamp();
                }
            }
        }
        $this->_last_modified = $modified;
    }

    /**
     * @param string $fetch_class
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function getResults( string $fetch_class = "" ): array {
        if( $this->_cache->isUpToDate($this->_last_modified) ) {
            Core::$_analyser->addTimer("PDOCache.getResults", $this->_cache->getFileName());
            Core::$_analyser->startTimer("PDOCache.getResults");
            $results = unserialize($this->_cache->loadFromCache(), array(false));
            Core::$_analyser->stopTimer("PDOCache.getResults");
        } else {
            Core::$_analyser->addTimer("PDOCache.getResults", $this->_pdo->getFinalizedQuery());
            Core::$_analyser->startTimer("PDOCache.getResults");
            if( $fetch_class !== "" ) {
                $results = $this->_pdo->execute()->fetchAll(PDO::FETCH_CLASS, $fetch_class);
            } else {
                $results = $this->_pdo->execute()->fetchAll();
            }
            Core::$_analyser->stopTimer("PDOCache.getResults");
            $this->_cache->saveToCache(serialize($results));
        }
        $elapsed_time = Core::$_analyser->getTimerElapsedTime("PDOCache.getResults");
        $label = Core::$_analyser->getTimerLabel("PDOCache.getResults");
        Core::$_storage::add("debug", $label." => elapsed time: ".round($elapsed_time * 1000, 4)."ms");

        return $results;
    }

}
