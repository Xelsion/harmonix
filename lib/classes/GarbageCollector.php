<?php
namespace lib\classes;

use DateTime;
use lib\App;
use lib\manager\ConnectionManager;

use Exception;
use lib\exceptions\SystemException;

/**
 * The Configuration type setSingleton
 * Collect all the configurations and stores them in an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class GarbageCollector {

    /**
     * The class constructor
     */
    public function __construct() {

    }

    /**
     * Calls the cleaning methods
     *
     * @return void
     *
     * @throws SystemException
     */
    public function clean() : void {
        $this->clearSessions();
    }

    /**
     * Deletes all expired sessions from the database
     *
     * @return void
     *
     * @throws SystemException
     */
    private function clearSessions() : void {
        $today = new DateTime();
        try {
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $pdo->prepareQuery("DELETE FROM sessions WHERE expired<:date");
            $pdo->bindParam("date", $today->format("Y-m-d H:i:s"));
            $pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}
