<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use models\ActorRoleModel;
use PDO;

/**
 * @inheritDoc
 *
 * @see ARepository
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
                ->Where(["id" => $id])
                ->prepareStatement()
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
                ->Where(["id" => $id])
                ->prepareStatement()
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
			$this->pdo->Select();
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
	 * Returns the parent role of the given role id
	 *
	 * @param int $id
	 * @return ActorRoleModel
	 * @throws SystemException
	 */
	public function getParentOf(int $id): ActorRoleModel {
		try {
			$role = $this->get($id);
			return $this->get($role->child_of);
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Returns all children of the given role id
	 *
	 * @param int $id
	 * @return array
	 * @throws SystemException
	 */
	public function getChildsOf(int $id): array {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("actor_roles")
				->Where(["child_of" =>  $id])
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
	 * @param ActorRoleModel $role
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(ActorRoleModel $role): void {
		try {
			// @formatter:off
            $this->pdo->Insert("actor_roles")
                ->Values([
					"child_of" => $role->child_of,
	                "name" => $role->name,
	                "rights_all" => $role->rights_all,
	                "rights_group" => $role->rights_group,
	                "rights_own" => $role->rights_own,
                ])
                ->prepareStatement()
                ->execute()
            ;
	        // @formatter:on
			$role->id = $this->pdo->lastInsertId();
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param ActorRoleModel $role
	 * @return void
	 * @throws SystemException
	 */
	public function updateObject(ActorRoleModel $role): void {
		if( $role->id === 0 || $role->is_protected ) {
			return;
		}

		try {
			// @formatter:off
            $this->pdo->Update("actor_roles")
                ->Values([
					"child_of" => $role->child_of,
	                "name" => $role->name,
	                "rights_all" => $role->rights_all,
	                "rights_group" => $role->rights_group,
	                "rights_own" => $role->rights_own,
                ])
                ->Where(["id" => $role->id])
                ->prepareStatement()
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param ActorRoleModel $role
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(ActorRoleModel $role): void {
		if( $role->id === 0 || $role->is_protected ) {
			return;
		}

		try {
			$childs = $this->getChildsOf($role->id);
			foreach( $childs as $child ) {
				$child->child_of = $role->child_of;
				$this->updateObject($child);
			}

			// @formatter:off
            $this->pdo->Delete()->From("actor_roles")
                ->Where(["id" => $role->id])
                ->prepareStatement()
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}