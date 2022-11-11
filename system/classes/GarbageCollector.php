<?php

namespace system\classes;

use DateTime;

use system\System;

/**
 * The Configuration type singleton
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
     */
    public function clean() : void {
        $this->clearSessions();
    }

    /**
     * Deletes all expired sessions from the database
     *
     * @return void
     */
    private function clearSessions() : void {
        $today = new DateTime();
        $pdo = System::$Core->connection_manager->getConnection("mvc");
        $pdo->prepareQuery("DELETE FROM sessions WHERE expired<:date");
        $pdo->bindParam("date", $today->format("Y-m-d H:i:s"));
        $pdo->execute();
    }

}
