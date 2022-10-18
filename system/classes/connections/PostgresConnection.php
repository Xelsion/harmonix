<?php
namespace system\classes\connections;

use PDO;
use system\abstracts\ADBConnection;

class PostgresConnection extends ADBConnection {

    private array $_options = array(
        PDO::ATTR_PERSISTENT         => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_KEY_PAIR,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
    );

    public function getConnectionString(): string {
        return sprintf("pgsql:host=%s;port=%d;dbname=%s", $this->_host, $this->_port, $this->_dbname);
    }

    public function getConnectionOptions(): array {
        return $this->_options;
    }
}
