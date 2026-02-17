<?php

namespace lib\core\classes;

use DateInterval;
use DateTime;
use Exception;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;

/**
 * The GarbageCollector
 * Deletes outdated and unnecessary data from the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
readonly class GarbageCollector {

	private ConnectionManager $connectionManager;

	/**
	 * The class constructor
	 */
	public function __construct(ConnectionManager $connectionManager) {
		$this->connectionManager = $connectionManager;
	}

	/**
	 * Calls the cleaning methods
	 *
	 * @return void
	 *
	 * @throws SystemException
	 */
	public function clean(): void {
		$this->clearSessions();
		$this->clearStorage();
		$this->clearTokens();
	}

	/**
	 * Deletes all expired sessions from the database
	 *
	 * @return void
	 *
	 * @throws SystemException
	 */
	private function clearSessions(): void {
		$today = new DateTime();
		try {
			$pdo = $this->connectionManager->getConnection("mvc");
			$pdo->prepareQuery("DELETE FROM sessions WHERE expired<:date");
			$pdo->setData(["date" => $today->format("Y-m-d H:i:s")]);
			$pdo->execute();
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @return void
	 * @throws SystemException
	 */
	private function clearStorage(): void {
		$today = new DateTime();
		$lifetime = $today->add(DateInterval::createFromDateString("-90 days"));
		try {
			$pdo = $this->connectionManager->getConnection("mvc");
			$pdo->prepareQuery("DELETE FROM action_storage WHERE created<:date");
			$pdo->setData(["date" => $lifetime->format("Y-m-d H:i:s")]);
			$pdo->execute();
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Deletes all expired tokens from the database
	 *
	 * @return void
	 *
	 * @throws SystemException
	 */
	private function clearTokens(): void {
		$today = new DateTime();
		try {
			$pdo = $this->connectionManager->getConnection("mvc");
			$pdo->prepareQuery("DELETE FROM tokens WHERE expired<:date");
			$pdo->setData(["date" => $today->format("Y-m-d H:i:s")]);
			$pdo->execute();
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}
