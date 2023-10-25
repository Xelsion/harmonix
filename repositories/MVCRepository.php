<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use models\entities\Session;
use models\entities\Token;
use models\SessionModel;
use PDO;

/**
 * @inheritDoc
 *
 * @see ARepository
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class MVCRepository extends ARepository {

	/**
	 * @throws SystemException
	 */
	public function __construct() {
		$cm = App::getInstanceOf(ConnectionManager::class);
		$this->pdo = $cm->getConnection("mvc");
	}

	/**
	 * @param string $id
	 * @return SessionModel
	 * @throws SystemException
	 */
	public function getSession(string $id): SessionModel {
		try {
			// @formatter:off
            $session = $this->pdo->Select()
                ->From("sessions")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $id)
                ->fetchMode(PDO::FETCH_CLASS, SessionModel::class)
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$session ) {
				$session = new SessionModel(App::$config);
			}
			return $session;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param string $id
	 * @return array
	 * @throws SystemException
	 */
	public function getSessionAsArray(string $id): array {
		try {
			// @formatter:off
            $result = $this->pdo->Select()
                ->From("sessions")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $id)
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$result ) {
				return array();
			}
			return $result;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 *
	 * @param Session $session
	 * @return void
	 * @throws SystemException
	 */
	public function createSession(SessionModel $session): void {
		try {
			// @formatter:off
            $this->pdo->Insert("sessions")
                ->Columns(["id", "actor_id", "ip", "expired"])
                ->prepareStatement()
                    ->withParam(':id', $session->id)
                    ->withParam(':actor_id', $session->actor_id, PDO::PARAM_INT)
                    ->withParam(':ip', $session->ip)
                    ->withParam(':expired', $session->expired)
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param Session $session
	 * @return void
	 * @throws SystemException
	 */
	public function updateSession(SessionModel $session): void {
		if( $session->id === "" ) {
			return;
		}
		try {
			$curr_id = $session->id;
			if( $session->_rotate_session ) {
				$session->id = StringHelper::getGuID();
			}
			// @formatter:off
            $this->pdo->Update("sessions")
                ->Set(["id", "actor_id", "as_actor", "ip", "expired"])
                ->Where("id=:curr_id")
                ->prepareStatement()
                    ->withParam(':id', $session->id)
                    ->withParam(':curr_id', $curr_id)
                    ->withParam(':actor_id', $session->actor_id, PDO::PARAM_INT)
                    ->withParam(':as_actor', $session->as_actor, PDO::PARAM_INT)
                    ->withParam(':ip', $session->ip)
                    ->withParam(':expired', $session->expired)
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param Session $session
	 * @return void
	 * @throws SystemException
	 */
	public function deleteSession(SessionModel $session): void {
		if( $session->id === "" ) {
			return;
		}
		try {
			// @formatter:off
            $this->pdo->Delete("sessions")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $session->id)
                ->fetchMode(PDO::FETCH_CLASS, Token::class)
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param string $id
	 * @return Token
	 * @throws SystemException
	 */
	public function getToken(string $id): Token {
		try {
			// @formatter:off
            return $this->pdo->Select()
                ->From("tokens")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $id)
                ->fetchMode(PDO::FETCH_CLASS, Token::class)
                ->execute()
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
	public function getTokenAsArray(string $id): array {
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
	 * @param Token $token
	 * @return void
	 * @throws SystemException
	 */
	public function createToken(Token $token): void {
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
	 * @param Token $token
	 * @return void
	 * @throws SystemException
	 */
	public function updateToken(Token $token): void {
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
	 * @param Token $token
	 * @return void
	 * @throws SystemException
	 */
	public function deleteToken(Token $token): void {
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