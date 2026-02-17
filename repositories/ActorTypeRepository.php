<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use models\ActorTypeModel;
use PDO;

/**
 * @inheritDoc
 *
 * @see ARepository
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class ActorTypeRepository extends ARepository {

	/**
	 * @throws SystemException
	 */
	public function __construct() {
		$cm = App::getInstanceOf(ConnectionManager::class);
		$this->pdo = $cm->getConnection("mvc");
	}

	/**
	 * @param int $id
	 * @return mixed
	 * @throws SystemException
	 */
	public function get(int $id): ActorTypeModel {
		try {
			// @formatter:off
            $actor_type = $this->pdo->Select()
                ->From("actor_types")
                ->Where(["id" => $id])
                ->prepareStatement()
                ->fetchMode(PDO::FETCH_CLASS, ActorTypeModel::class)
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$actor_type ) {
				$actor_type = new ActorTypeModel();
			}
			return $actor_type;
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
            $actor_type = $this->pdo->Select()
                ->From("actor_types")
                ->Where(["id" => $id])
                ->prepareStatement()
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$actor_type ) {
				$actor_type = array();
			}
			return $actor_type;
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
                ->From("actor_types")
                ->prepareStatement()
                ->fetchMode(PDO::FETCH_CLASS, ActorTypeModel::class)
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
		return $this->findIn("actor_types", ActorTypeModel::class, $conditions, $order, $direction, $limit, $page);
	}

	/**
	 * Returns the total number of actor types
	 *
	 * @return int
	 * @throws SystemException
	 */
	public function getNumRows(): int {
		// @formatter:off
        $result = $this->pdo->Select("COUNT(DISTINCT id)")->As("num_count")
            ->From("actor_types")
            ->prepareStatement()
            ->execute()
            ->fetch()
        ;
	    // @formatter:on
		return (int)$result["num_count"];
	}

	/**
	 * @param ActorTypeModel $type
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(ActorTypeModel $type): void {
		try {
			// @formatter:off
            $this->pdo->Insert("actor_types")
                ->Values(["name" => $type->name])
                ->prepareStatement()
                ->execute()
            ;
	        // @formatter:on
			$type->id = $this->pdo->lastInsertId();
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param ActorTypeModel $type
	 * @return void
	 * @throws SystemException
	 */
	public function updateObject(ActorTypeModel $type): void {
		if( $type->id === 0 || $type->is_protected ) {
			return;
		}
		try {
			// @formatter:off
            $this->pdo->Update("actor_types")
                ->Values(["name" => $type->name])
                ->Where(["id" => $type->id])
                ->prepareStatement()
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param ActorTypeModel $type
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(ActorTypeModel $type): void {
		if( $type->id === 0 || $type->is_protected ) {
			return;
		}
		try {
			// @formatter:off
            $this->pdo->Delete()->From("actor_types")
                ->Where(["id" => $type->id])
                ->prepareStatement()
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}