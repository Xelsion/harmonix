<?php
namespace lib\classes\connections;

use PDO;
use lib\abstracts\ADBConnection;

/**
 * The MySqlConnection class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class MySqlConnection extends ADBConnection {

    private array $_options = array(
        PDO::ATTR_PERSISTENT         => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_FOUND_ROWS => true
    );

    /**
     * Returns a formate connection string for a MySQL database
     * @return string
     */
    public function getConnectionString(): string {
        return sprintf("mysql:host=%s;port=%d;dbname=%s", $this->host, $this->port, $this->dbname);
    }

    /**
     * Returns the MySQL options that will be used in each query
     *
     * @return array
     */
    public function getConnectionOptions(): array {
        return $this->_options;
    }
}
