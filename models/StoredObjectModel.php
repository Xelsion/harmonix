<?php

namespace models;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use models\entities\StoredObject;
use repositories\ActionStorageRepository;

class StoredObjectModel extends StoredObject {

	private readonly ActionStorageRepository $repository;

	/**
	 * @param int $id
	 * @throws SystemException
	 */
	public function __construct(int $id = 0) {
		$this->repository = App::getInstanceOf(ActionStorageRepository::class);

		if( $id > 0 ) {
			try {
				$data = $this->repository->getAsArray($id);

				if( !empty($data) ) {

					$this->id = (int)$data["id"];
					$this->actor_id = (int)$data["actor_id"];
					$this->action = $data["action"];
					$this->connection_key = $data["connection_key"];
					$this->table_name = $data["table_name"];
					$this->obj_before = $data["obj_before"];
					$this->obj_after = $data["obj_after"];
					$this->created = $data["created"];
				}
			} catch( Exception $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
	}

}