<?php

namespace system\classes;

use DateTime;

use system\Core;

/**
 * The Configuration type singleton
 * Collect all the configurations and stores them in an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class GarbageCollector {

    public function __construct() {

    }

    public function clean() : void {
        $this->clearSessions();
    }

    private function clearSessions() : void {
        $today = new DateTime();
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $pdo->prepare("DELETE FROM sessions WHERE expired<:date");
        $pdo->bindParam("date", $today->format("Y-m-d H:i:s"));
        $pdo->execute();
    }

}
