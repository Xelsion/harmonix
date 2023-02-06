<?php
namespace lib\core\classes;

use DateTime;
use Exception;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;

/**
 * The Configuration type setAsSingleton
 * Collect all the configurations and stores them in an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
readonly class GarbageCollector {

    /**
     * The class constructor
     */
    public function __construct( private ConnectionManager $connectionManager ) {

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
            $pdo = $this->connectionManager->getConnection("mvc");
            $pdo->prepareQuery("DELETE FROM sessions WHERE expired<:date");
            $pdo->bindParam("date", $today->format("Y-m-d H:i:s"));
            $pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}
