<?php

namespace models;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use models\entities\AccessPermission;

/**
 * The ActorModel Permissions
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessPermissionModel extends AccessPermission {

	private ?ActorRoleModel $role = null;

	/**
	 * The class constructor
	 * If id is 0 it will return an empty actor
	 *
	 * @throws SystemException
	 */
	public function __construct() {
		if( $this->role_id > 0 ) {
			try {
				$this->role = App::getInstanceOf(ActorRoleModel::class, null, ["id" => $this->role_id]);
			} catch( Exception $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
	}

	/**
	 * Returns the role of this permission
	 *
	 * @return ActorRoleModel|null
	 */
	public function getRole(): ?ActorRoleModel {
		return $this->role;
	}
}
