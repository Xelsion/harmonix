<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use models\SessionModel;
use PDO;

/**
 * @inheritDoc
 *
 * @see ARepository
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class SessionRepository extends ARepository {

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
	public function get(string $id): SessionModel {
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
	public function getAsArray(string $id): array {
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
	 * @return array
	 * @throws SystemException
	 */
	public function getAll(): array {
		try {
			// @formatter:off
			return $this->pdo->Select()
				->From("sessions")
				->prepareStatement()
				->fetchMode(PDO::FETCH_CLASS, SessionModel::class)
				->execute()
				->fetchAll()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 *
	 * @param SessionModel $session
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(SessionModel $session): void {
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
	 * @param SessionModel $session
	 * @return void
	 * @throws SystemException
	 */
	public function updateObject(SessionModel $session): void {
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
	 * @param SessionModel $session
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(SessionModel $session): void {
		if( $session->id === "" ) {
			return;
		}
		try {
			// @formatter:off
			$this->pdo->Delete("sessions")
				->Where("id=:id")
				->prepareStatement()
					->withParam(":id", $session->id)
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}