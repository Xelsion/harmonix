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
				->Where(["id" => $id])
				->prepareStatement()
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
				->Where(["id" => $id])
				->prepareStatement()
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
				->Values([
					"id" => $session->id,
					"actor_id" => $session->actor_id,
					"ip" => $session->ip,
					"expired" => $session->expired,
				])
				->prepareStatement()
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
				->Values([
					"id" => $session->id,
					"actor_id" => $session->actor_id,
					"as_actor" => $session->as_actor,
					"ip" => $session->ip,
					"expired" => $session->expired,
				])
				->Where(["id" => $curr_id])
				->prepareStatement()
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
			$this->pdo->Delete()->From("sessions")
				->Where(["id" => $session->id])
				->prepareStatement()
				->execute()
			;
			// @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}