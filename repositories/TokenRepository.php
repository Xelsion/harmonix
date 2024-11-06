<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use models\entities\Token;
use models\TokenModel;
use PDO;

/**
 * @inheritDoc
 *
 * @see ARepository
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class TokenRepository extends ARepository {

	/**
	 * @throws SystemException
	 */
	public function __construct() {
		$cm = App::getInstanceOf(ConnectionManager::class);
		$this->pdo = $cm->getConnection("mvc");
	}

	/**
	 * @param string $id
	 * @return Token
	 * @throws SystemException
	 */
	public function get(string $id): Token {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("tokens")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $id)
				->fetchMode(PDO::FETCH_CLASS, Token::class)
				->execute()
				->fetch()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param string $id
	 * @return array
	 * @throws SystemException
	 */
	public function getAsArray(string $id): array {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("tokens")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $id)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
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
				->From("tokens")
				->prepareStatement()
				->fetchMode(PDO::FETCH_CLASS, Token::class)
				->execute()
				->fetchAll()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param TokenModel $token
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(TokenModel $token): void {
		try {
			// @formatter:off
			$this->pdo->Insert("tokens")
				->Columns(["id", "expired"])
				->prepareStatement()
					->withParam(":id", $token->id)
					->withParam(":expired", $token->expired)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param TokenModel $token
	 * @return void
	 * @throws SystemException
	 */
	public function updateObject(TokenModel $token): void {
		if( $token->id === "" ) {
			return;
		}
		try {
			// @formatter:off
			$this->pdo->Update("tokens")
				->Set(["expired"])
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $token->id)
					->withParam(":expired", $token->expired)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param TokenModel $token
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(TokenModel $token): void {
		if( $token->id === "" ) {
			return;
		}
		try {
			// @formatter:off
			$this->pdo->Delete("tokens")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $token->id)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}