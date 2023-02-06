<?php
namespace lib\core\database\connections;

use lib\core\blueprints\ADBConnection;
use PDO;

/**
 * The MsSqlConnection class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class MsSqlConnection extends ADBConnection {

    private array $_options = array(
        PDO::ATTR_PERSISTENT         => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
    );

    /**
     * Returns a formate connection string for a MSSQL database
     *
     * @return string
     */
    public function getConnectionString(): string {
        return sprintf("sqlsrv:server=%s;port=%d;Database=%s", $this->host, $this->port, $this->dbname);
    }

    /**
     * Returns the MSSQL options that will be used in each query
     *
     * @return array
     */
    public function getConnectionOptions(): array {
        return $this->_options;
    }
}
