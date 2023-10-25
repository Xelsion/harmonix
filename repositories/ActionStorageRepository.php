<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use models\StoredObjectModel;
use PDO;

class ActionStorageRepository extends ARepository {

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
	public function get(int $id): StoredObjectModel {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("action_storage")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $id)
				->fetchMode(PDO::FETCH_CLASS, StoredObjectModel::class)
				->execute()
				->fetch();
			// @formatter:on
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
			return $this->pdo->Select()
				->From("action_storage")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $id)
				->execute()
				->fetch();
			// @formatter:on
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
				->From("action_storage")
				->prepareStatement()
				->fetchMode(PDO::FETCH_CLASS, StoredObjectModel::class)
				->execute()
				->fetchAll();
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Returns an array of actors as ActorModel with the given conditions or an empty array
	 *
	 * @param array $conditions
	 * @param string|null $order
	 * @param string|null $direction
	 * @param int $limit
	 * @param int $page
	 * @return array
	 * @throws SystemException
	 */
	public function find(array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1): array {
		return $this->findIn("action_storage", StoredObjectModel::class, $conditions, $order, $direction, $limit, $page);
	}

	/**
	 * @param string $action
	 * @param string $connection_key
	 * @param string $table_name
	 * @param object|null $obj_before
	 * @param object|null $obj_after
	 * @return void
	 * @throws SystemException
	 */
	public function storeAction(string $action, string $connection_key, string $table_name, object $obj_before = null, object $obj_after = null): void {
		try {
			$obj_before = ($obj_before !== null) ? serialize($obj_before) : null;
			$obj_after = ($obj_after !== null) ? serialize($obj_after) : null;

			// @formatter:off
			$this->pdo->Insert("action_storage")
				->Columns(["actor_id", "action", "connection_key", "table_name", "obj_before", "obj_after"])
				->prepareStatement()
					->withParam(':actor_id', App::$curr_actor->id, PDO::PARAM_INT)
					->withParam(':action', $action)
					->withParam(':connection_key', $connection_key)
					->withParam(':table_name', $table_name)
					->withParam(':obj_before', $obj_before, $this->getParamType($obj_before))
					->withParam(':obj_after', $obj_after, $this->getParamType($obj_after))
				->execute();
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param StoredObjectModel $stored_obj
	 * @return void
	 * @throws SystemException
	 */
	public function undoAction(StoredObjectModel $stored_obj): void {
		try {
			if( $stored_obj->id > 0 ) {
				$connection_key = $stored_obj->connection_key;
				$table_name = $stored_obj->table_name;

				$cm = App::getInstanceOf(ConnectionManager::class);
				$repository = $cm->getConnection($connection_key);

				if( $stored_obj->action === "delete" ) {
					$object = unserialize($stored_obj->obj_before, ["allowed_classes" => true]);
					$obj_params = get_object_vars($object);
					$repository->Insert($table_name)->Columns(array_keys($obj_params))->prepareStatement();
					foreach( $obj_params as $col => $value ) {
						$repository->withParam(":" . $col, $value, $this->getParamType($value));
					}
					$repository->execute();
				} else if( $stored_obj->action === "update" ) {
					$object = unserialize($stored_obj->obj_before, ["allowed_classes" => true]);
					$obj_params = get_object_vars($object);
					if( isset($obj_params['id']) ) {
						$obj_id = (int)$obj_params['id'];
						unset($obj_params['id']);
						if( $obj_id > 0 ) {
							$repository->Update($table_name)
								->Set(array_keys($obj_params))
								->Where("id=:id")
								->prepareStatement()
							;
							$repository->withParam(":id", $obj_id);
							foreach( $obj_params as $col => $value ) {
								$repository->withParam(":" . $col, $value, $this->getParamType($value));
							}
							$repository->execute();
						}
					}
				} else if( $stored_obj->action === "create" ) {
					$object = unserialize($stored_obj->obj_after, ["allowed_classes" => true]);
					$repository->Delete($table_name)
						->Where("id=:id")
						->prepareStatement()
						->withParam(":id", $object->id)
						->execute()
					;
				} else {
					throw new SystemException(__FILE__, __LINE__, "Unknown action");
				}
			}
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param int $id
	 * @return void
	 * @throws SystemException
	 */
	private function deleteStoredObject(int $id): void {
		// @formatter:off
		$this->pdo->Delete("action_storage")
			->Where("id=:id")
			->prepareStatement()
				->withParam(":id", $id)
			->execute();
		// @formatter:on
	}

	/**
	 * Returns the total number of actors
	 *
	 * @return int
	 * @throws SystemException
	 */
	public function getNumRows(): int {
		// @formatter:off
		$result = $this->pdo->Select("COUNT(DISTINCT id)")
			->As("num_count")
			->From("action_storage")
			->prepareStatement()
			->execute()
			->fetch()
		;
		// @formatter:on
		return (int)$result["num_count"];
	}

	/**
	 * @param $param
	 * @return int
	 */
	private function getParamType($param): int {
		if( is_null($param) ) {
			return PDO::PARAM_NULL;
		}
		if( is_int($param) ) {
			return PDO::PARAM_INT;
		}
		if( is_bool($param) ) {
			return PDO::PARAM_BOOL;
		}
		return PDO::PARAM_STR;
	}

}