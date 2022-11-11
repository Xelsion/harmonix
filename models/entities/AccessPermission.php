<?php

namespace models\entities;

use Exception;
use PDO;
use system\abstracts\AEntity;
use system\exceptions\SystemException;
use system\System;

/**
 * The AccessPermissionModel entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessPermission extends AEntity {

	// The columns
	public int $actor_id = 0;
	public int $role_id = 0;
	public string $domain = "";
	public ?string $controller = null;
	public ?string $method = null;
    public string $created = "";
    public ?string $updated = null;
    public ?string $deleted = null;

	/**
	 * The constructor loads the database content into this object.
	 * If id is 0 the entity will be empty
	 *
	 */
	public function __construct() {

	}

    /**
     * @inheritDoc
     */
	public function create(): void {
		try {
			$pdo = System::$Core->connection_manager->getConnection("mvc");
			$sql = "INSERT INTO access_permissions (actor_id, role_id, domain, controller, method) VALUES (:actor_id, :role_id, :domain, :controller, :method)";
			$pdo->prepareQuery($sql);
			$pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
			$pdo->bindParam(':role_id', $this->role_id, PDO::PARAM_INT);
			$pdo->bindParam(':domain', $this->domain);
			$pdo->bindParam(':controller', $this->controller);
			$pdo->bindParam(':method', $this->method);
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
