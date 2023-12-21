<?php

namespace models;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use models\entities\Token;
use repositories\MVCRepository;

class TokenModel extends Token {

	/**
	 * The class constructor
	 *
	 * @param string $id
	 *
	 * @throws SystemException
	 */
	public function __construct(string $id = "") {
		if( $id !== "" ) {
			try {
				$mvc_repo = App::getInstanceOf(MVCRepository::class);
				$token_data = $mvc_repo->getTokenAsArray($id);
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