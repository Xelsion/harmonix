<?php

namespace models\entities;

use Exception;
use PDO;
use system\abstracts\ACacheableEntity;
use system\Core;
use system\exceptions\SystemException;

class AccessRestriction extends ACacheableEntity {

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
            $pdo = Core::$_connection_manager->getConnection("mvc");
            $sql = "INSERT INTO access_restrictions (domain, controller, method, restriction_type, role_id) VALUES (:domain, :controller, :method, :restriction_type, :role_id)";
            $pdo->prepare($sql);
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
