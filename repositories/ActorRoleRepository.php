<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use models\ActorRoleModel;
use models\entities\ActorRole;
use PDO;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class ActorRoleRepository extends ARepository {

	/**
	 * @throws SystemException
	 */
	public function __construct() {
		$cm = App::getInstanceOf(ConnectionManager::class);
		$this->pdo = $cm->getConnection("mvc");
	}

	/**
	 * @param int $id
	 * @return ActorRoleModel
	 * @throws SystemException
	 */
	public function get(int $id): ActorRoleModel {
		try {
			// @formatter:off
            $actor_role = $this->pdo->Select()
                ->From("actor_roles")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $id, PDO::PARAM_INT)
                ->fetchMode(PDO::FETCH_CLASS, ActorRoleModel::class)
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$actor_role ) {
				$actor_role = new ActorRoleModel();
			}
			return $actor_role;
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param int $id
	 * @return array
	 * @throws SystemException
	 */
	public function getAsArray(int $id): array {
		try {
			// @formatter:off
            $actor_role = $this->pdo->Select()
                ->From("actor_roles")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $id, PDO::PARAM_INT)
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$actor_role ) {
				$actor_role = array();
			}
			return $actor_role;
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
                ->From("actor_roles")
                ->prepareStatement()
                ->fetchMode(PDO::FETCH_CLASS, ActorRoleModel::class)
                ->execute()
                ->fetchAll()
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
		return $this->findIn("actor_roles", ActorRoleModel::class, $conditions, $order, $direction, $limit, $page);
	}

	/**
	 * Returns the total number of actor roles
	 *
	 * @return int
	 * @throws SystemException
	 */
	public function getNumRows(): int {
		// @formatter:off
        $result = $this->pdo->Select("COUNT(DISTINCT id)")->As("num_count")
            ->From("actor_roles")
            ->prepareStatement()
            ->execute()
            ->fetch()
        ;
	    // @formatter:on
		return (int)$result["num_count"];
	}

	/**
	 * @param ActorRole $role
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(ActorRole $role): void {
		try {
			// @formatter:off
            $this->pdo->Insert("actor_roles")
                ->Columns(["child_of", "name", "rights_all", "rights_group", "rights_own"])
                ->prepareStatement()
                    ->withParam(':child_of', $role->child_of, PDO::PARAM_INT)
                    ->withParam(':name', $role->name)
                    ->withParam(':rights_all', $role->rights_all, PDO::PARAM_INT)
                    ->withParam(':rights_group', $role->rights_group, PDO::PARAM_INT)
                    ->withParam(':rights_own', $role->rights_own, PDO::PARAM_INT)
                ->execute()
            ;
	        // @formatter:on
			$role->id = $this->pdo->lastInsertId();
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param ActorRole $role
	 * @return void
	 * @throws SystemException
	 */
	public function updateObject(ActorRole $role): void {
		if( $role->id === 0 || $role->is_protected ) {
			return;
		}

		try {
			// @formatter:off
            $this->pdo->Update("actor_roles")
                ->Set(["child_of", "name", "rights_all", "rights_group", "rights_own"])
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $role->id, PDO::PARAM_INT)
                    ->withParam(':child_of', $role->child_of, PDO::PARAM_INT)
                    ->withParam(':name', $role->name)
                    ->withParam(':rights_all', $role->rights_all, PDO::PARAM_INT)
                    ->withParam(':rights_group', $role->rights_group, PDO::PARAM_INT)
                    ->withParam(':rights_own', $role->rights_own, PDO::PARAM_INT)
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param ActorRole $role
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(ActorRole $role): void {
		if( $role->id === 0 || $role->is_protected ) {
			return;
		}

		try {
			// @formatter:off
            $this->pdo->Delete("actor_roles")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $role->id, PDO::PARAM_INT)
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}