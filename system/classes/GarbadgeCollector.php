<?php

namespace system\classes;

use DateTime;
use system\Core;

class GarbadgeCollector {

    public function __construct() {

    }

    public function clean() {
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