<?php
namespace system\classes\connections;

use PDO;

use system\abstracts\ADBConnection;

class MsSqlConnection extends ADBConnection {

    private array $_options = array(
        PDO::ATTR_PERSISTENT         => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
    );

    public function getConnectionString(): string {
        return sprintf("sqlsrv:server=%s;port=%d;Database=%s", $this->_host, $this->_port, $this->_dbname);
    }

    public function getConnectionOptions(): array {
        return $this->_options;
    }
}
