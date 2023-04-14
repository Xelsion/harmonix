<?php
namespace lib\core\database\connections;

use lib\core\blueprints\ADBConnection;
use lib\core\enums\DbType;
use PDO;

/**
 * The MsSqlConnection class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class MsSqlConnection extends ADBConnection {

    private array $_options = array(
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
    );

    /**
     * @inheritDoc
     */
    public function getConnectionString(): string {
        return sprintf("sqlsrv:Server=%s;Database=%s", $this->host, $this->dbname);
    }

    /**
     * @inheritDoc
     */
    public function getConnectionOptions(): array {
        return $this->_options;
    }

    /**
     * @inheritDoc
     */
    public function getType(): DbType {
        return DbType::MsSQL;
    }

}
