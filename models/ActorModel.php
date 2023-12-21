<?php

namespace models;

use Exception;
use lib\App;
use lib\core\enums\ActorType;
use lib\core\exceptions\SystemException;
use models\entities\Actor;
use repositories\AccessPermissionRepository;
use repositories\ActorRepository;
use repositories\ActorRoleRepository;

/**
 * The ActorModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorModel extends Actor {

	// a collection of all permission this user contains
	public array $permissions = array();

	public array $data = array();

	/**
	 * The class constructor
	 * If id is 0 it will return an empty actor
	 *
	 * @param int $id
	 *
	 * @throws SystemException
	 */
	public function __construct(int $id = 0) {
		if( $id > 0 ) {
			try {
				$actor_repo = App::getInstanceOf(ActorRepository::class);
				$actor_data = $actor_repo->getAsArray($id);
				if( !empty($actor_data) ) {
					$this->id = (int)$actor_data["id"];
					$this->type_id = (int)$actor_data["type_id"];
					$this->first_name = $actor_data["first_name"];
					$this->last_name = $actor_data["last_name"];
					$this->email = $actor_data["email"];
					$this->password = $actor_data["password"];
					$this->login_fails = (int)$actor_data["login_fails"];
					$this->login_disabled = (bool)$actor_data["login_disabled"];
					$this->created = $actor_data["created"];
					$this->updated = ($actor_data["updated"] !== "") ? $actor_data["updated"] : null;
					$this->deleted = ($actor_data["deleted"] !== "") ? $actor_data["deleted"] : null;
					$this->initPermission();
				}
			} catch( Exception $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
	}

	/**
	 * Returns if the actor is of type developer or not
	 *
	 * @return bool
	 *
	 */
	public function isDeveloper(): bool {
		return ($this->type_id === ActorType::Developer->value);
	}

	/**
	 * Returns the actor role for the given controller method
	 * If non is setClass for a specific method it will look for a
	 * controller role and if non is setClass too, it will look for
	 * the domain role
	 *
	 * @param string $controller
	 * @param string $method
	 * @param mixed|string $domain
	 *
	 * @return ActorRoleModel
	 *
	 * @throws SystemException
	 */
	public function getRole(string $controller, string $method, string $domain = SUB_DOMAIN): ActorRoleModel {
		try {
			// do we have a loaded actor object?
			if( $this->id > 0 && !$this->deleted ) {
				// no permission restriction is set?
				if( empty($this->permissions) ) {
					$this->initPermission();
				}

				// check if there is a permission setClass for this method if so return the actor role
				if( isset($this->permissions[$domain][$controller][$method]) ) {
					return $this->permissions[$domain][$controller][$method];
				}

				// check if there is a permission setClass for this controller if so return the actor role
				if( isset($this->permissions[$domain][$controller][null]) ) {
					return $this->permissions[$domain][$controller][null];
				}

				// check if there is a permission setClass for this domain if so return the actor role
				if( isset($this->permissions[$domain][null][null]) ) {
					return $this->permissions[$domain][null][null];
				}
			}

			// actor object is not loaded, so we return the default actor role
			$actor_role_repo = App::getInstanceOf(ActorRoleRepository::class);
			$result = $actor_role_repo->find([["is_default", "=", 1]]);
			if( count($result) === 1 ) {
				return $result[0];
			}

			// if no default actor role could be found return an empty actor role
			return App::getInstanceOf(ActorRoleModel::class);
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Deletes all Permission sets for the current actor
	 *
	 * @return bool
	 *
	 * @throws SystemException
	 */
	public function deletePermissions(): bool {
		if( $this->id > 0 ) {
			$access_permission_repo = App::getInstanceOf(AccessPermissionRepository::class);
			$access_permission_repo->deleteAccessPermissionFor($this);
			return true;
		}
		return false;
	}

	/**
	 * Collects all permission for this user
	 *
	 * @throws SystemException
	 */
	private function initPermission(): void {
		try {
			$access_permission_repo = App::getInstanceOf(AccessPermissionRepository::class);
			$permissions = $access_permission_repo->getAccessPermissionFor($this);
			foreach( $permissions as $permission ) {
				$this->permissions[$permission->domain][$permission->controller][$permission->method] = $permission->getRole();
			}
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}
