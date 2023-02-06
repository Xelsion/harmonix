<?php
namespace models\entities;

use Exception;
use lib\App;
use lib\core\blueprints\AEntity;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use PDO;

/**
 * The AccessRestriction entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessRestriction extends AEntity {

    public string $domain = "";
    public ?string $controller = null;
    public ?string $method = null;
    public int $restriction_type = 0;
    public int $role_id = 0;
    public string $created = "";
    public ?string $updated = null;
    public ?string $deleted = null;

    public function __construct( int $id = 0 ) {

    }

    /**
     * @inheritDoc
     */
    public function create(): void {
        try {
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $sql = "INSERT INTO access_restrictions (domain, controller, method, restriction_type, role_id) VALUES (:domain, :controller, :method, :restriction_type, :role_id)";
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':domain', $this->domain);
            $pdo->bindParam(':controller', $this->controller);
            $pdo->bindParam(':method', $this->method);
            $pdo->bindParam(':restriction_type', $this->restriction_type, PDO::PARAM_INT);
            $pdo->bindParam(':role_id', $this->role_id, PDO::PARAM_INT);
            $pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @inheritDoc
     */
    public function update(): void {

    }

    /**
     * @inheritDoc
     */
    public function delete(): bool {
        return false;
    }

}
