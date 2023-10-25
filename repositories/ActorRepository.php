<?php

namespace repositories;

use DateTime;
use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use models\AccessPermissionModel;
use models\ActorModel;
use models\ActorRoleModel;
use models\ActorTypeModel;
use models\entities\Actor;
use PDO;

/**
 * @inheritDoc
 *
 * @see ARepository
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class ActorRepository extends ARepository {

	/**
	 * @throws SystemException
	 */
	public function __construct() {
		$cm = App::getInstanceOf(ConnectionManager::class);
		$this->pdo = $cm->getConnection("mvc");
	}

	/**
	 * Return the actor with the given id or an empty actor as ActorModel
	 *
	 * @param int $id
	 * @return ActorModel
	 * @throws SystemException
	 */
	public function get(int $id): ActorModel {
		try {
			// @formatter:off
			$actor = $this->pdo->Select()
				->From("actors")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $id, PDO::PARAM_INT)
				->fetchMode(PDO::FETCH_INTO, App::getInstanceOf(ActorModel::class))
				->execute()
				->fetch()
			;
			// @formatter:on
			if( !$actor ) {
				$actor = new ActorModel();
			}
			return $actor;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Return the actor with the given id or an empty actor as array
	 *
	 * @param int $id
	 * @return array
	 * @throws SystemException
	 */
	public function getAsArray(int $id): array {
		try {
			// @formatter:off
			$actor = $this->pdo->Select()
				->From("actors")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $id, PDO::PARAM_INT)
				->execute()
				->fetch()
			;
			// @formatter:on
			if( !$actor ) {
				$actor = array();
			}
			return $actor;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Return the actor with the given email or an empty actor as ActorModel
	 *
	 * @param string $email
	 * @return ActorModel
	 * @throws SystemException
	 */
	public function getByLogin(string $email): ActorModel {
		try {
			// @formatter:off
			$actor = $this->pdo->Select()
				->From("actors")
				->Where("email=:email")
					->And("login_disabled=0")
					->And("deleted")->isNull()
				->prepareStatement()
					->withParam(":email", $email)
				->fetchMode(PDO::FETCH_CLASS, ActorModel::class)
				->execute()
				->fetch()
			;
			// @formatter:on
			if( !$actor ) {
				$actor = new ActorModel();
			}
			return $actor;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Returns am array of actors as ActorModel
	 *
	 * @return array of Actor's
	 * @throws SystemException
	 */
	public function getAll(): array {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("actors")
				->prepareStatement()
				->fetchMode(PDO::FETCH_CLASS, ActorModel::class)
				->execute()
				->fetchAll()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
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
		return $this->findIn("actors", ActorModel::class, $conditions, $order, $direction, $limit, $page);
	}

	/**
	 * Returns the actor type of the given ActorModel
	 *
	 * @param ActorModel $actor
	 * @return mixed
	 * @throws SystemException
	 */
	public function getActorType(ActorModel $actor): ActorTypeModel {
		return App::getInstanceOf(ActorTypeModel::class, null, ["id" => $actor->type_id]);
	}

	/**
	 * Returns the actor role for the given controller method
	 * If non is setClass for a specific method it will look for a
	 * controller role and if non is setClass too, it will look for
	 * the domain role
	 *
	 * @param ActorModel $actor
	 * @param string $controller
	 * @param string $method
	 * @param mixed|string $domain
	 *
	 * @return ActorRoleModel
	 *
	 * @throws SystemException
	 */
	public function getActorRole(ActorModel $actor, string $controller, string $method, string $domain = SUB_DOMAIN): ActorRoleModel {
		try {
			// do we have a loaded actor object?
			if( $actor->id > 0 ) {
				// check role for the given domain, controller and method
				// @formatter:off
				$result = $this->pdo->Select("role_id")
					->From("access_permissions")
					->Where("actor_id=:actor_id")
						->And("domain=:domain")
						->And("controller=:controller")
						->And("method=:method=:method")
					->prepareStatement()
						->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
						->withParam(":domain", $domain)
						->withParam(":controller", $controller)
						->withParam(":method", $method)
					->execute()
					->fetch()
				;
				// @formatter:on
				if( $result ) {
					return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["role_id"]]);
				}

				// check role for the given domain and controller
				// @formatter:off
				$result = $this->pdo->Select("role_id")
					->From("access_permissions")
					->Where("actor_id=:actor_id")
						->And("domain=:domain")
						->And("controller=:controller")
					->prepareStatement()
						->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
						->withParam(":domain", $domain)
						->withParam(":controller", $controller)
					->execute()
					->fetch()
				;
				// @formatter:on
				if( $result ) {
					return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["role_id"]]);
				}

				// check role for the given domain
				// @formatter:off
				$result = $this->pdo->Select("role_id")
					->From("access_permissions")
					->Where("actor_id=:actor_id")
						->And("domain=:domain")
					->prepareStatement()
						->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
						->withParam(":domain", $domain)
					->execute()
					->fetch()
				;
				// @formatter:on
				if( $result ) {
					return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["role_id"]]);
				}
			} else {
				// @formatter:off
				$result = $this->pdo->Select("id")
					->From("actor_roles")
					->Where("is_default=1")
					->prepareStatement()
					->execute()
					->fetch()
				;
				// @formatter:on
				if( $result ) {
					return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["id"]]);
				}
			}

			return App::getInstanceOf(ActorRoleModel::class);
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}


	/**
	 * Returns an array of AccessPermissionModel's for the given ActorModel
	 *
	 * @param ActorModel $actor
	 * @return array of AccessPermission's
	 * @throws SystemException
	 */
	public function getActorPermissions(ActorModel $actor): array {
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
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Returns the total number of actors
	 *
	 * @return int
	 * @throws SystemException
	 */
	public function getNumRows(): int {
		return (int)$this->pdo->getNumRowsOfTable("actors");
	}

	/**
	 * @param Actor $actor
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(ActorModel $actor): void {
		try {
			$actor->password = StringHelper::getBCrypt($actor->password);

			// store this action
			$storage_repo = App::getInstanceOf(ActionStorageRepository::class);
			$storage_repo->storeAction("create", "mvc", "actors", null, $actor->getAsEntity());

			// @formatter:off
			$this->pdo->Insert("actors")
				->Columns(["type_id", "email", "password", "first_name", "last_name", "login_fails", "login_disabled"])
				->prepareStatement()
					->withParam(':type_id', $actor->type_id)
					->withParam(':email', $actor->email)
					->withParam(':password', $actor->password)
					->withParam(':first_name', $actor->first_name)
					->withParam(':last_name', $actor->last_name)
					->withParam(':login_fails', $actor->login_fails, PDO::PARAM_INT)
					->withParam(':login_disabled', $actor->login_disabled, PDO::PARAM_INT)
				->execute()
			;
			// @formatter:on
			$actor->id = $this->pdo->lastInsertId();
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param Actor $actor
	 * @return void
	 * @throws SystemException
	 */
	public function updateObject(ActorModel $actor): void {
		try {
			// @formatter:off
			$row = $this->pdo->Select("password")
				->From("actors")
				->Where("id=:id")
				->prepareStatement()
					->withParam(':id', $actor->id, PDO::PARAM_INT)
				->execute()
				->fetch()
			;
			// @formatter:on
			if( !empty($row) ) {
				if( $actor->password !== '' && $row["password"] !== $actor->password ) {
					$actor->password = StringHelper::getBCrypt($actor->password);
				} else {
					$actor->password = $row["password"];
				}

				// store this action
				$obj_orig = $this->get($actor->id);
				$storage_repo = App::getInstanceOf(ActionStorageRepository::class);
				$storage_repo->storeAction("update", "mvc", "actors", $obj_orig->getAsEntity(), $actor->getAsEntity());

				// @formatter:off
				$this->pdo->Update("actors")
					->Set(["email", "password", "first_name", "last_name", "login_fails", "login_disabled", "deleted"])
					->Where("id=:id")
					->prepareStatement()
						->withParam(':id', $actor->id, PDO::PARAM_INT)
						->withParam(':email', $actor->email)
						->withParam(':password', $actor->password)
						->withParam(':first_name', $actor->first_name)
						->withParam(':last_name', $actor->last_name)
						->withParam(':login_fails', $actor->login_fails, PDO::PARAM_INT)
						->withParam(':login_disabled', $actor->login_disabled, PDO::PARAM_INT)
						->withParam(':deleted', $actor->deleted)
					->execute()
				;
				// @formatter:on
			}
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}

	}

	/**
	 * Deletes the given actor from the database.
	 * Hint: instead of deleting the record will be marked as "deleted"
	 *
	 * @param Actor $actor
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(Actor $actor): void {
		if( $actor->id > 0 ) {
			try {
				// @formatter:off
				$this->pdo->Update("actors")
					->Set(["deleted", "login_disabled"])
					->Where("id=:id")
					->prepareStatement()
						->withParam(':id', $actor->id, PDO::PARAM_INT)
						->withParam(':login_disabled', 1, PDO::PARAM_INT)
						->withParam(':deleted', (new DateTime())->format("Y-m-d H:i:s"))
					->execute()
				;
				// @formatter:on
			} catch( Exception $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
	}

	/**
	 * Deletes the given actor from the database.
	 * Hint: instead of deleting the record will be marked as "deleted"
	 *
	 * @param Actor $actor
	 * @return void
	 * @throws SystemException
	 */
	public function undeleteObject(Actor $actor): void {
		if( $actor->id > 0 ) {
			try {
				// @formatter:off
				$this->pdo->Update("actors")
					->Set(["deleted", "login_disabled"])
					->Where("id=:id")
					->prepareStatement()
						->withParam(':id', $actor->id, PDO::PARAM_INT)
						->withParam(':login_disabled', 0, PDO::PARAM_INT)
						->withParam(':deleted', null, PDO::PARAM_NULL)
					->execute()
				;
				// @formatter:on
			} catch( Exception $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
	}

}