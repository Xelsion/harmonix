<?php

namespace models;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use models\entities\AccessRestriction;
use repositories\AccessRestrictionRepository;

/**
 * The AccessRestrictionModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessRestrictionModel extends AccessRestriction {

	private readonly AccessRestrictionRepository $restriction_repository;

	/**
	 * The class constructor
	 *
	 * @param int $id
	 * @throws SystemException
	 */
	public function __construct(int $id = 0) {
		$this->restriction_repository = App::getInstanceOf(AccessRestrictionRepository::class);
		if( $id > 0 ) {
			try {
				$restriction_data = $this->restriction_repository->getAsArray($id);
				if( !empty($restriction_data) ) {
					$this->id = (int)$restriction_data["id"];
					$this->domain = $restriction_data["domain"];
					$this->controller = ($restriction_data["controller"] !== "") ? $restriction_data["controller"] : null;
					$this->method = ($restriction_data["method"] !== "") ? $restriction_data["method"] : null;
					$this->restriction_type = (int)$restriction_data["restriction_type"];
					$this->role_id = (int)$restriction_data["role_id"];
					$this->created = $restriction_data["created"];
					$this->updated = ($restriction_data["updated"] !== "") ? $restriction_data["updated"] : null;
					$this->deleted = ($restriction_data["deleted"] !== "") ? $restriction_data["deleted"] : null;
				}
			} catch( Exception $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
	}

	/**
	 * Returns this Model as Entity
	 *
	 * @return AccessRestriction
	 */
	public function getAsEntity(): AccessRestriction {
		$entity = new AccessRestriction();
		$entity->id = $this->id;
		$entity->domain = $this->domain;
		$entity->controller = $this->controller;
		$entity->method = $this->method;
		$entity->restriction_type = $this->restriction_type;
		$entity->role_id = $this->role_id;
		$entity->created = $this->created;
		$entity->updated = $this->updated;
		$entity->deleted = $this->deleted;
		return $entity;
	}

}
