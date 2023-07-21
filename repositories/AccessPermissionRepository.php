<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use models\AccessPermissionModel;
use models\entities\AccessPermission;
use models\entities\Actor;
use PDO;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class AccessPermissionRepository extends ARepository {

	/**
	 * @throws SystemException
	 */
	public function __construct() {
		$cm = App::getInstanceOf(ConnectionManager::class);
		$this->pdo = $cm->getConnection("mvc");
	}

	/**
	 * @param int $actor_id
	 * @param int $role_id
	 * @param string $domain
	 * @param string|null $controller
	 * @param string|null $method
	 * @return AccessPermissionModel
	 * @throws SystemException
	 */
	public function get(int $actor_id, int $role_id, string $domain, ?string $controller, ?string $method): AccessPermissionModel {
		try {
			// @formatter:off
            $access_permission = $this->pdo->Select()
                ->From("access_permission")
                ->Where("actor_id=:actor_id")
                    ->And("role_id=:role_id")
                    ->And("domain=:domain")
                    ->And("controller=:controller")
                    ->And("method=:method")
                ->prepareStatement()
                    ->withParam(":actor_id", $actor_id, PDO::PARAM_INT)
                    ->withParam(":role_id", $role_id, PDO::PARAM_INT)
                    ->withParam(":domain", $domain)
                    ->withParam(":controller", $controller)
                    ->withParam(":method", $method)
                ->fetchMode(PDO::FETCH_CLASS, AccessPermissionModel::class)
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$access_permission ) {
				$access_permission = new AccessPermissionModel();
			}
			return $access_permission;
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param int $actor_id
	 * @param int $role_id
	 * @param string $domain
	 * @param string|null $controller
	 * @param string|null $method
	 * @return array
	 * @throws SystemException
	 */
	public function getAsArray(int $actor_id, int $role_id, string $domain, ?string $controller, ?string $method): array {
		try {
			// @formatter:off
			$access_permission = $this->pdo->Select()
				->From("access_permission")
				->Where("actor_id=:actor_id")
					->And("role_id=:role_id")
					->And("domain=:domain")
					->And("controller=:controller")
					->And("method=:method")
				->prepareStatement()
					->withParam(":actor_id", $actor_id, PDO::PARAM_INT)
					->withParam(":role_id", $role_id, PDO::PARAM_INT)
					->withParam(":domain", $domain)
					->withParam(":controller", $controller)
					->withParam(":method", $method)
				->execute()
				->fetch()
			;
			// @formatter:on
			if( !$access_permission ) {
				$access_permission = array();
			}
			return $access_permission;
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @return array
	 * @throws SystemException
	 */
	public function getAll(): array {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("access_permissions")
				->fetchMode(PDO::FETCH_CLASS, AccessPermissionModel::class)
				->execute()
				->fetchAll()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param Actor $actor
	 * @return array
	 * @throws SystemException
	 */
	public function getAccessPermissionFor(Actor $actor): array {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("access_permissions")
				->Where("actor_id=:actor_id")
				->prepareStatement()
					->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
				->fetchMode(PDO::FETCH_CLASS, AccessPermissionModel::class)
				->execute()
				->fetchAll()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param Actor $actor
	 * @return void
	 * @throws SystemException
	 */
	public function deleteAccessPermissionFor(Actor $actor): void {
		try {
			// @formatter:off
			$this->pdo->Delete("access_permissions")
				->Where("actor_id=:actor_id")
				->prepareStatement()
				->withParam(":actor:id", $actor->id, PDO::PARAM_INT)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param array $conditions
	 * @param string|null $order
	 * @param string|null $direction
	 * @param int $limit
	 * @param int $page
	 * @return array
	 * @throws SystemException
	 */
	public function find(array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1): array {
		return $this->findIn("access_permissions", AccessPermissionModel::class, $conditions, $order, $direction, $limit, $page);
	}

	/**
	 * Returns the total number of access permissions
	 *
	 * @return int
	 * @throws SystemException
	 */
	public function getNumRows(): int {
		// @formatter:off
		$result = $this->pdo->Select("COUNT(DISTINCT *)")->As("num_count")
			->From("access_permissions")
			->prepareStatement()
			->execute()
			->fetch()
		;
		// @formatter:on
		return (int)$result["num_count"];
	}

	/**
	 * @param AccessPermission $permission
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(AccessPermission $permission): void {
		try {
			// @formatter:off
			$this->pdo->Insert("access_permissions")
				->Columns(["actor_id", "role_id", "domain", "controller", "method"])
				->prepareStatement()
					->withParam(':actor_id', $permission->actor_id, PDO::PARAM_INT)
					->withParam(':role_id', $permission->role_id, PDO::PARAM_INT)
					->withParam(':domain', $permission->domain)
					->withParam(':controller', $permission->controller)
					->withParam(':method', $permission->method)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param AccessPermission $permission
	 * @return void
	 * @throws SystemException
	 */
	public function updateObject(AccessPermission $permission): void {
		// no updating required
	}

	/**
	 * @param AccessPermission $permission
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(AccessPermission $permission): void {
		try {
			// @formatter:off
			$this->pdo->Delete("access_permissions")
				->Where("actor_id=:actor_id")
					->And("role_id=:role_id")
					->And("domain=:domain")
					->And("controller=:controller")
					->And("method=:method")
				->prepareStatement()
					->withParam(':actor_id', $permission->actor_id, PDO::PARAM_INT)
					->withParam(':role_id', $permission->role_id, PDO::PARAM_INT)
					->withParam(':domain', $permission->domain)
					->withParam(':controller', $permission->controller)
					->withParam(':method', $permission->method)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}