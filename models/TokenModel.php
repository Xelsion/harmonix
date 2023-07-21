<?php

namespace models;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use repositories\MVCRepository;

class TokenModel extends entities\Token {

	/**
	 * The class constructor
	 *
	 * @param string $id
	 *
	 * @throws SystemException
	 */
	public function __construct(string $id = "") {
		$mvc_repository = App::getInstanceOf(MVCRepository::class);
		if( $id !== "" ) {
			try {
				$token_data = $mvc_repository->getTokenAsArray($id);
				if( !empty($token_data) ) {
					$this->id = $token_data["id"];
					$this->expired = $token_data["expired"];
				}
			} catch( Exception $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
	}

}