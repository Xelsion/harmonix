<?php

namespace lib\core\blueprints;

use Exception;
use lib\core\database\PDOConnection;
use lib\core\exceptions\SystemException;

/**
 * A Repository provides all function needed to communicate with the source of the data.
 * Mostly each repository handles a single source (like a table in a database) but in some cases it could be more than
 * a single source.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class ARepository {

	protected PDOConnection $pdo;

	/**
	 * @param string $table_name
	 * @param string $class_name
	 * @param array $conditions
	 * @param string|null $order
	 * @param string|null $direction
	 * @param int $limit
	 * @param int $page
	 * @return array
	 * @throws SystemException
	 */
	public function findIn(string $table_name, string $class_name, array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1): array {
		try {
			if( is_null($this->pdo) || !isset($this->pdo) ) {
				throw new SystemException(__FILE__, __LINE__, self::class . ": pdo not set!");
			}
			$qb = $this->pdo->Select()->From($table_name);
			if( !empty($conditions) ) {
				$qb->Where($conditions);
			}
			if( !empty($order) ) {
				$qb->OrderBy($order, strtoupper((string)$direction));
			}

			if( $limit > 0 ) {
				$offset = ($page - 1) * $limit;
				$qb->Limit($limit, $offset);
			}
			$stmt = $qb->prepareStatement()->fetchMode(\PDO::FETCH_CLASS, $class_name)->execute();
			return $stmt->fetchAll();
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}