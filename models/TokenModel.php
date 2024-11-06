<?php

namespace models;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use models\entities\Token;
use repositories\TokenRepository;

/**
 * The TokenModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
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
				$token_repo = App::getInstanceOf(TokenRepository::class);
				$token_data = $token_repo->getAsArray($id);
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