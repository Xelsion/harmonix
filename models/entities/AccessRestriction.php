<?php

namespace models\entities;

use PDO;
use \system\abstracts\AEntity;
use system\Core;

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
        if( $id > 0 ) {
            $pdo = Core::$_connection_manager->getConnection("mvc");
            $pdo->prepare("SELECT * FROM access_restrictions WHERE id=:id");
            $pdo->bindParam(":id", $id, PDO::PARAM_INT);
            $pdo->setFetchMode(PDO::FETCH_INTO, $this);
            $pdo->execute()->fetch();
        }
    }

    /**
     * @inheritDoc
     */
    public function create(): void {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $sql = "INSERT INTO access_restrictions (domain, controller, method, restriction_type, role_id) VALUES (:domain, :controller, :method, :restriction_type, :role_id)";
        $pdo->prepare($sql);
        $pdo->bindParam(':domain', $this->domain);
        $pdo->bindParam(':controller', $this->controller);
        $pdo->bindParam(':method', $this->method);
        $pdo->bindParam(':restriction_type', $this->restriction_type, PDO::PARAM_INT);
        $pdo->bindParam(':role_id', $this->role_id, PDO::PARAM_INT);
        $pdo->execute();
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