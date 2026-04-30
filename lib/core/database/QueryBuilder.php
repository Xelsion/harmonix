<?php

namespace lib\core\database;

use lib\core\enums\DbType;
use lib\core\enums\QBStep;
use lib\core\enums\QueryType;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use PDO;
use PDOStatement;

#start
class QueryBuilder {

	private PDO $pdo;
	public ?PDOStatement $stmt;
	public QBStep $curr_step;
	public string $sql = "";
	public array $params = [];
	protected ?QueryType $query_type = null;
	protected array $where_clauses = [];
	protected array $after_wheres = [];
	protected ?DbType $db_type;
	private bool $join_open = false;
	private bool $required_order = false;
	private bool $order_is_set = false;
	private bool $alias_possible = false;
	private bool $alias_required = false;
	private bool $group_prepared = false;
	private int $param_counter = 0;
	private int $subquery_count = 0;
	private array $allowed_operators = ["=", ">", "<", ">=", "<=", "<>", "!=", "LIKE", "NOT LIKE"];
	private array $allowed_special_operators = ["IN", "NOT IN", "BETWEEN", "NOT BETWEEN", "RAW"];

	public function __construct(PDO $pdo, ?DbType $db_type = null) {
		$this->pdo = $pdo;
		if( $db_type !== null ) {
			$this->db_type = $db_type;
		}
		$this->curr_step = QBStep::NONE;
	}

	// ==================== Start functions ====================

	/**
	 * @param mixed|null $columns
	 * @return $this
	 * @throws SystemException
	 */
	public function Select(mixed $columns = null): static {
		$this->checkSQL(QBStep::START);
		$this->query_type = QueryType::SELECT;
		$cols = array();
		if( is_null($columns) ) {
			$cols[] = "*";
		} elseif( is_array($columns) ) {
			foreach( $columns as $key => $value ) {
				if( $value instanceof self ) {
					$sql = $this->addSubquery($value);
					$cols[] = "({$sql}) AS {$key}";
				} elseif( is_string($key) ) {
					$cols[] = "{$value} AS {$key}";
				} else {
					$cols[] = $value;
				}
			}
		} else {
			$cols[] = $columns;
		}

		$this->sql = "SELECT " . implode(", ", $cols);
		return $this;
	}

	/**
	 * @param string $table
	 * @return $this
	 * @throws SystemException
	 */
	public function Insert(string $table): static {
		$this->checkSQL(QBStep::START);
		$this->query_type = QueryType::INSERT;
		$this->sql = "INSERT INTO " . $table;
		return $this;
	}

	/**
	 * @param string $table
	 * @return $this
	 * @throws SystemException
	 */
	public function Update(string $table): static {
		$this->checkSQL(QBStep::START);
		$this->query_type = QueryType::UPDATE;
		$this->sql = "UPDATE " . $table;
		return $this;
	}

	/**
	 * @return $this
	 * @throws SystemException
	 */
	public function Delete(): static {
		$this->checkSQL(QBStep::START);
		$this->query_type = QueryType::DELETE;
		$this->sql = "DELETE ";
		return $this;
	}

	/**
	 * @param string $table
	 * @return $this
	 * @throws SystemException
	 */
	public function Truncate(string $table): static {
		$this->checkSQL(QBStep::START);
		$this->query_type = QueryType::TRUNCATE;
		$this->sql = "TRUNCATE " . $table;
		return $this;
	}

	// ==================== From & Join ====================

	/**
	 * @param mixed|null $tables
	 * @return $this
	 * @throws SystemException
	 */
	public function From(mixed $tables = null): static {
		$this->checkSQL(QBStep::FROM);
		if( $tables instanceof self ) {
			$sql = $this->addSubquery($tables);
			$this->sql .= " FROM ({$sql})";
			$this->alias_possible = true;
			$this->alias_required = true;
		} elseif( is_array($tables) ) {
			$this->sql .= " FROM " . implode(", ", $tables);
		} else {
			$this->sql .= " FROM " . $tables;
			$this->alias_possible = true;
		}
		return $this;
	}

	/**
	 * @param string $table
	 * @return $this
	 * @throws SystemException
	 */
	public function innerJoin(string $table): static {
		$this->checkSQL(QBStep::JOIN);
		$this->sql .= " INNER JOIN " . $table;
		$this->alias_possible = true;
		return $this;
	}

	/**
	 * @param string $table
	 * @return $this
	 * @throws SystemException
	 */
	public function leftJoin(string $table): static {
		$this->checkSQL(QBStep::JOIN);
		$this->sql .= " LEFT JOIN " . $table;
		$this->alias_possible = true;
		return $this;
	}

	/**
	 * @param string $table
	 * @return $this
	 * @throws SystemException
	 */
	public function rightJoin(string $table): static {
		$this->checkSQL(QBStep::JOIN);
		$this->sql .= " RIGHT JOIN " . $table;
		$this->alias_possible = true;
		return $this;
	}

	/**
	 * @param string|array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function On(string|array $conditions): static {
		$this->checkSQL(QBStep::JOIN_ON);
		if( is_array($conditions) && !empty($conditions) ) {
			$this->sql .= " ON " . $this->compileConditions($conditions);
		} else if( !StringHelper::isNullOrEmpty($conditions) ) {
			$this->sql .= " ON " . $conditions;
		} else {
			throw new SystemException(__FILE__, __LINE__, "ON must contain at least one condition");
		}
		return $this;
	}

	/**
	 * @param string $alias
	 * @return $this
	 * @throws SystemException
	 */
	public function As(string $alias): static {
		$this->checkSQL(QBStep::AS);
		$this->sql .= " AS " . $alias;
		$this->alias_possible = false;
		return $this;
	}


	// ==================== Where ====================

	/**
	 * @param array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function Where(array $conditions = []): static {
		$this->checkSQL(QBStep::WHERE);
		if( empty($conditions) ) {
			$this->where_clauses[] = ['logic' => 'AND', 'data' => null];
			$this->group_prepared = true;
		} else {
			$this->where_clauses[] = ['logic' => 'AND', 'data' => $conditions];
		}
		return $this;
	}

	/**
	 * @param array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function And(array $conditions = []): static {
		$this->checkSQL(QBStep::WHERE_ADDS);
		if( empty($conditions) ) {
			$this->where_clauses[] = ['logic' => 'AND', 'data' => null];
			$this->group_prepared = true;
		} else {
			$this->where_clauses[] = ['logic' => 'AND', 'data' => $conditions];
		}
		return $this;
	}

	/**
	 * @param array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function Or(array $conditions = []): static {
		$this->checkSQL(QBStep::WHERE_ADDS);
		if( empty($conditions) ) {
			$this->where_clauses[] = ['logic' => 'OR', 'data' => null];
			$this->group_prepared = true;
		} else {
			$this->where_clauses[] = ['logic' => 'OR', 'data' => $conditions];
		}
		return $this;
	}

	/**
	 * @param array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function Group(array $conditions): static {
		$this->checkSQL(QBStep::GROUP);
		$this->where_clauses[] = ['logic' => '()', 'data' => $conditions];
		return $this;
	}

	/**
	 * Compiles all conditions and adds the resulting sql string to the current sql
	 *
	 * @return string
	 * @throws SystemException
	 */
	protected function compileFinalWhere(): string {
		if( empty($this->where_clauses) ) {
			return "";
		}

		$sql = 'WHERE';
		$parts = [];
		$first = true;

		foreach( $this->where_clauses as $clause ) {
			$logic = $clause['logic'];
			$data = $clause['data'];
			if( $logic === '()' ) {
				// Group erzeugt IMMER ein geklammertes Fragment
				$fragment = '(' . $this->compileConditions($data) . ')';
			} else {
				// AND / OR
				$fragment = $this->compileConditions($data);
			}
			if( $first ) {
				$parts[] = $fragment;
				$first = false;
			} else {
				$parts[] = "{$logic} {$fragment}";
			}
		}
		return " WHERE " . implode(" ", $parts);
	}

	/**
	 * Compiles the given $conditions and returns them as sql string
	 *
	 * @param array $conditions
	 * @return string
	 * @throws SystemException
	 */
	protected function compileConditions(array $conditions): string {
		$parts = [];
		$currentLogic = 'AND';
		$expectCondition = true;

		foreach( $conditions as $key => $value ) {

			// AND / OR
			if( is_string($value) && in_array(strtoupper($value), ['AND', 'OR'], true) ) {
				if( $expectCondition ) {
					throw new SystemException(__FILE__, __LINE__, "Operator without left condition");
				}
				$currentLogic = strtoupper($value);
				$expectCondition = true;
				continue;
			}

			// EXISTS / NOT EXISTS
			if( is_string($key) && in_array(strtoupper($key), ['EXISTS', 'NOT EXISTS'], true) ) {
				if( !$value instanceof self && (!is_string($value) || trim($value) === '') ) {
					throw new SystemException(__FILE__, __LINE__, "EXISTS requires subquery or raw SQL");
				}
				$sql = ($value instanceof self) ? $this->addSubquery($value) : $value;
				$fragment = strtoupper($key) . " ({$sql})";
			} elseif( is_int($key) && is_array($value) ) { // Group (numeric Key)
				$inner = $this->compileConditions($value);
				if( $inner === "" ) {
					continue;
				}
				$fragment = '(' . $inner . ')';
			} elseif( is_int($key) ) {
				throw new SystemException(__FILE__, __LINE__, "Invalid numeric condition");
			} else { // Normale Condition
				$fragment = $this->compileSingleCondition($key, $value);
			}

			// Leere Bedingungen skippen (wichtig!)
			if( $fragment === '' ) {
				continue;
			}

			// add to parts
			if( empty($parts) ) {
				$parts[] = $fragment;
			} else {
				$parts[] = $currentLogic . ' ' . $fragment;
			}
			$expectCondition = false;
			$currentLogic = 'AND';
		}

		// final check
		if( $expectCondition && !empty($parts) ) {
			throw new SystemException(__FILE__, __LINE__, "Query cannot end with an operator");
		}
		return (is_array($parts) && !empty($parts)) ? implode(' ', $parts) : '';
	}

	/**
	 * Analyses the given column and value from a condition and returns a fitting sql string with a placeholder for the value.
	 * The value will be stored in an internal array for later safe injection.
	 *
	 * @param string $column
	 * @param mixed $value
	 * @return string
	 * @throws SystemException
	 */
	private function compileSingleCondition(string $column, mixed $value): string {
		// value IS NULL
		if( is_null($value) ) {
			return "{$column} IS NULL";
		}

		$placeholder = $this->makePlaceholderName($column);

		// =
		if( !is_array($value) ) {
			$this->addBindParam($placeholder, $value);
			return "{$column} = :{$placeholder}";
		}

		// Operatoren (Array)
		$operator = strtoupper(trim((string)key($value)));
		$val = current($value);

		if( !$this->isAllowedOperator($operator) ) {
			throw new SystemException(__FILE__, __LINE__, "Operator {$operator} is not allowed");
		}

		// Subquery helper
		$resolveSubquery = function($v) {
			return ($v instanceof self) ? $this->addSubquery($v) : $v;
		};

		switch( $operator ) {
			case 'BETWEEN':
			case 'NOT BETWEEN':
				if( !is_array($val) || count($val) !== 2 ) {
					throw new SystemException(__FILE__, __LINE__, "BETWEEN requires exactly 2 values");
				}
				$p1 = $placeholder . '_a';
				$p2 = $placeholder . '_b';
				$this->addBindParam($p1, $val[0]);
				$this->addBindParam($p2, $val[1]);
				return "{$column} {$operator} :{$p1} AND :{$p2}";

			case 'IN':
			case 'NOT IN':
				if( $val instanceof self ) {
					return "{$column} {$operator} ({$resolveSubquery($val)})";
				}
				$vals = (array)$val;
				if( count($vals) === 0 ) {
					return ($operator === 'IN') ? '1=0' : '1=1';
				}
				$placeholders = [];
				foreach( $vals as $i => $v ) {
					$p = $placeholder . '_' . $i;
					$placeholders[] = ":{$p}";
					$this->addBindParam($p, $v);
				}
				return "{$column} {$operator} (" . implode(', ', $placeholders) . ")";

			case 'RAW':
				if( !is_string($val) || trim($val) === '' ) {
					throw new SystemException(__FILE__, __LINE__, "RAW requires non-empty string");
				}
				return "{$column} {$val}";

			// Default operators (=, >, <, LIKE, etc.)
			default:
				if( $val instanceof self ) {
					return "{$column} {$operator} ({$resolveSubquery($val)})";
				}
				$this->addBindParam($placeholder, $val);
				return "{$column} {$operator} :{$placeholder}";
		}
	}

	/**
	 * returns if the given operator is allowed or not
	 *
	 * @param string $operator
	 * @return bool
	 */
	private function isAllowedOperator(string $operator): bool {
		return (in_array(strtoupper($operator), $this->allowed_operators, true) || in_array(strtoupper($operator), $this->allowed_special_operators, true));
	}

	/**
	 * add a QueryBuilder instance to this query as subquery
	 *
	 * @param QueryBuilder $subquery
	 * @return string
	 * @throws SystemException
	 */
	private function addSubquery(self $subquery): string {
		$this->checkSQL(QBStep::SUBQUERY);
		$subquery->prefixParameters("sq{$this->subquery_count}");
		foreach( $subquery->params as $k => $v ) {
			$this->params[$k] = $v;
		}
		$this->subquery_count++;
		return $subquery->sql;
	}

	// ==================== Order, Limit, Group, Having ====================

	/**
	 * @param mixed $columns
	 * @param string $order
	 * @return $this
	 * @throws SystemException
	 */
	public function OrderBy(mixed $columns, string $order = "ASC"): static {
		$order = strtoupper($order);
		if( !in_array($order, ['ASC', 'DESC'], true) ) {
			throw new SystemException(__FILE__, __LINE__, "Invalid ORDER direction");
		}
		$this->checkSQL(QBStep::ORDER_BY);
		$sql = " ORDER BY";
		if( is_array($columns) ) {
			$is_first = true;
			foreach( $columns as $col => $dir ) {
				$sql .= (($is_first) ? " " : ", ") . $col . " " . $dir;
				$is_first = false;
			}
		} else {
			$sql .= " " . $columns . " " . $order;
		}
		$this->after_wheres[] = $sql;
		return $this;
	}

	/**
	 * @param mixed $columns
	 * @return $this
	 * @throws SystemException
	 */
	public function GroupBy(mixed $columns): static {
		$this->checkSQL(QBStep::GROUP_BY);
		$this->after_wheres[] = is_array($columns) ? " GROUP BY " . implode(", ", $columns) : " GROUP BY " . $columns;
		return $this;
	}

	/**
	 * @param string $condition
	 * @return $this
	 * @throws SystemException
	 */
	public function Having(string $condition): static {
		$this->checkSQL(QBStep::HAVING);
		$this->after_wheres[] = " HAVING " . $condition;
		return $this;
	}

	/**
	 * @param int $limit
	 * @param int $offset
	 * @return $this
	 * @throws SystemException
	 */
	public function Limit(int $limit, int $offset = 0): static {
		$this->checkSQL(QBStep::LIMIT);
		switch( $this->db_type ) {
			case DbType::MySQL:
				$this->after_wheres[] = " LIMIT $limit OFFSET $offset";
				break;
			case DbType::MsSQL:
				$this->after_wheres[] = " OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";
				$this->required_order = true;
				break;
			case DbType::Postgres:
				$this->after_wheres[] = " OFFSET $offset LIMIT $limit";
				break;
		}
		return $this;
	}

	/**
	 * @param mixed|null $columns
	 * @return $this
	 * @throws SystemException
	 */
	public function Returning(mixed $columns = null): static {
		$this->checkSQL(QBStep::RETURNING);
		if( is_null($columns) ) {
			$this->after_wheres[] = " RETURNING *";
		} else if( is_array($columns) ) {
			$this->after_wheres[] = " RETURNING " . implode(", ", $columns);
		} else {
			$this->after_wheres[] = " RETURNING " . $columns;
		}
		return $this;
	}

	// ==================== Columns / Values ====================

	/**
	 * @param array $data
	 * @return $this
	 * @throws SystemException
	 */
	public function Values(array $data): static {
		$this->checkSQL(QBStep::VALUES);
		foreach( $data as $k => $v ) {
			$this->params[$k] = $v;
		}
		return $this->Columns($data);
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function Columns(array $data): static {
		$cols = array_keys($data);
		switch( $this->query_type ) {
			case QueryType::INSERT:
				$this->sql .= " (" . implode(", ", $cols) . ") VALUES (:" . implode(", :", $cols) . ")";
				break;
			case QueryType::UPDATE:
				$this->sql .= " SET " . implode(", ", array_map(static fn($c) => "$c=:$c", $cols));
				break;
			default:
		}
		return $this;
	}

	// ==================== Bind, Prepare, Fetch ====================

	/**
	 * @param string $placeholder
	 * @param mixed $value
	 * @return $this
	 */
	public function addBindParam(string $placeholder, mixed $value): static {
		$this->params[$placeholder] = $value;
		return $this;
	}

	/**
	 * add a prefix to all parameters
	 * @param string $prefix
	 * @return void
	 */
	private function prefixParameters(string $prefix): void {
		$newParams = [];
		foreach( $this->params as $key => $value ) {
			$newKey = "{$prefix}_{$key}";
			$this->sql = str_replace(":{$key}", ":{$newKey}", $this->sql);
			$newParams[$newKey] = $value;
		}
		$this->params = $newParams;
	}


	/**
	 * @param array $options
	 * @return $this
	 * @throws SystemException
	 */
	public function prepareStatement(array $options = []): static {
		if( $this->required_order && !$this->order_is_set ) {
			throw new SystemException(__FILE__, __LINE__, "MSSql requires an order if a limit is set.");
		}
		if( ($this->query_type === QueryType::DELETE || $this->query_type === QueryType::UPDATE) && empty($this->where_clauses) ) {
			throw new SystemException(__FILE__, __LINE__, "Refusing to run unsafe query without WHERE");
		}
		$this->sql .= $this->compileFinalWhere();
		$this->sql .= implode("", $this->after_wheres);
		$this->stmt = $this->pdo->prepare($this->sql, $options);
		$this->setData($this->params);
		return $this;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data): static {
		$this->params = $data;
		foreach( $data as $key => $value ) {
			if( !str_starts_with($key, ":") ) {
				$key = ":{$key}";
			}
			$this->stmt->bindValue($key, $value, $this->getPDOType($value));
		}
		return $this;
	}

	/**
	 * @param int $mode
	 * @param mixed|null $class
	 * @return $this
	 */
	public function fetchMode(int $mode = PDO::FETCH_ASSOC, mixed $class = null): static {
		$this->stmt->setFetchMode($mode, $class);
		return $this;
	}

	/**
	 * @param array $options
	 * @return PDOStatement
	 */
	public function execute(array $options = []): PDOStatement {
		$this->stmt->execute($options);
		return $this->stmt;
	}

	// ==================== Helpers ====================

	/**
	 * @param mixed $value
	 * @return int
	 */
	public function getPDOType(mixed $value): int {
		return match (gettype($value)) {
			'integer' => PDO::PARAM_INT,
			'boolean' => PDO::PARAM_BOOL,
			'NULL' => PDO::PARAM_NULL,
			default => PDO::PARAM_STR,
		};
	}

	/**
	 * Returns a unique parameter name
	 *
	 * @param string $base
	 * @return string
	 */
	private function makePlaceholderName(string $base): string {
		$base = preg_replace('/\W+/', '_', $base); // nur Buchstaben/Ziffern/_
		return $base . '_' . ($this->param_counter++);
	}


	/**
	 * @param QBStep $step
	 * @return void
	 * @throws SystemException
	 */
	private function checkSQL(QBStep $step): void {
		$order_error = false;
		$err_msg = "";

		if( $this->alias_required && $step->value !== QBStep::AS->value ) {
			throw new SystemException(__FILE__, __LINE__, $err_msg = "At this point an alias (As) is required!.");
		}

		if( $this->group_prepared && $step->value !== QBStep::GROUP->value ) {
			throw new SystemException(__FILE__, __LINE__, $err_msg = "At this point an Group is required!.");
		}

		if( !$this->alias_possible && $step->value !== QBStep::AS->value ) {
			$this->alias_possible = false;
		}

		if( $this->curr_step->value !== QBStep::NONE->value && $this->query_type->value === QueryType::TRUNCATE->value ) {
			throw new SystemException(__FILE__, __LINE__, $err_msg = "TRUNCATE does not allow additional SQL parameters");
		}

		if( $this->join_open && $step->value !== QBStep::JOIN_ON->value && $step->value !== QBStep::AS->value ) {
			throw new SystemException(__FILE__, __LINE__, $err_msg = "After a JOIN|AS is a ON required");
		}

		switch( $step ) {
			case QBStep::GROUP:
				if( !$this->group_prepared ) {
					$order_error = true;
					$err_msg = "Groups must be initiated with and empty And(), Or() or Where()";
				} else {
					$this->group_prepared = false;
				}
				break;
			case QBStep::SUBQUERY:
				// SELECT
				if( $this->query_type === QueryType::SELECT && !in_array($this->curr_step, [
						QBStep::START,
						QBStep::FROM,
						QBStep::WHERE,
						QBStep::WHERE_ADDS,
						QBStep::HAVING
					], true) ) {
					$order_error = true;
					$err_msg = "Subqueries are only allowed in SELECT, FROM, WHERE or HAVING";
					break;
				}

				// INSERT
				if( $this->query_type === QueryType::INSERT && $this->curr_step === QBStep::VALUES ) {
					$order_error = true;
					$err_msg = "Subqueries are not allowed inside INSERT VALUES";
					break;
				}

				// UPDATE
				if( $this->query_type === QueryType::UPDATE && $this->curr_step === QBStep::FROM ) {
					if( $this->db_type === DbType::Postgres || $this->db_type === DbType::MsSQL ) {
						break;
					}
					$order_error = true;
					$err_msg = "UPDATE ... FROM subqueries are only supported in PostgreSQL and MSSQL";
					break;
				}

				if( $this->query_type === QueryType::UPDATE && !in_array($this->curr_step, [
						QBStep::VALUES,
						QBStep::WHERE,
						QBStep::WHERE_ADDS
					], true) ) {
					$order_error = true;
					$err_msg = "Subqueries in UPDATE statements are only allowed in SET or WHERE clauses";
					break;
				}

				// DELETE
				if( $this->query_type === QueryType::DELETE && !in_array($this->curr_step, [
						QBStep::WHERE,
						QBStep::WHERE_ADDS
					], true) ) {
					$order_error = true;
					$err_msg = "Subqueries in DELETE statements are only allowed in WHERE clauses";
					break;
				}


				// TRUNCATE
				if( $this->query_type === QueryType::TRUNCATE ) {
					$order_error = true;
					$err_msg = "Subqueries are not allowed in TRUNCATE statements";
					break;
				}
				break;
			case(QBStep::START):
				if( $this->curr_step->value !== QBStep::NONE->value ) {
					$order_error = true;
					$err_msg = "A SQL must start with a SELECT, INSERT, UPDATE, DELETE or TRUNCATE";
				}
				break;
			case(QBStep::VALUES):
				if( ($this->query_type->value === QueryType::INSERT->value || $this->query_type->value === QueryType::UPDATE->value) && $this->curr_step->value !== QBStep::START->value ) {
					$order_error = true;
					$err_msg = "VALUES can be set directly after INSERT or UPDATE";
				}
				break;
			case(QBStep::FROM):
				if( ($this->query_type->value === QueryType::SELECT->value || $this->query_type->value === QueryType::DELETE->value) && $this->curr_step->value !== QBStep::START->value ) {
					$err_msg = "FROM can be set directly after SELECT or DELETE";
					$order_error = true;
				}
				break;
			case(QBStep::JOIN):
				if( $this->curr_step->value !== QBStep::FROM->value && $this->curr_step->value !== QBStep::JOIN_ON->value ) {
					$order_error = true;
					$err_msg = "JOINS can be set directly after FROM|ON";
				} else {
					$this->join_open = true;
				}
				break;
			case(QBStep::JOIN_ON):
				if( !$this->join_open ) {
					$order_error = true;
					$err_msg = "ON can be set directly after JOINS";
				} else {
					$this->join_open = false;
				}
				break;
			case(QBStep::AS):
				if( !$this->alias_possible ) {
					$order_error = true;
					$err_msg = "AS can be set after FROM|JOINS";
				}
				$this->alias_required = false;
				break;
			case(QBStep::WHERE):
				if( ($this->query_type->value === QueryType::SELECT->value || $this->query_type->value === QueryType::DELETE->value) && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$err_msg = "WHERE can be set after FROM|JOIN in an SELECT|DELETE statement";
					$order_error = true;
				} else if( $this->query_type->value === QueryType::UPDATE->value && !($this->curr_step->value >= QBStep::VALUES->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$err_msg = "WHERE can be set after VALUES in an UPDATE statement";
					$order_error = true;
				}
				break;
			case(QBStep::WHERE_ADDS):
				if( ($this->query_type->value === QueryType::SELECT->value || $this->query_type->value === QueryType::UPDATE->value || $this->query_type->value === QueryType::DELETE->value) && !($this->curr_step->value >= QBStep::WHERE->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$err_msg = "AND|OR can be set after WHERE|OR|AND in an SELECT|UPDATE|DELETE statement";
					$order_error = true;
				}
				break;
			case(QBStep::GROUP_BY):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::WHERE->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$order_error = true;
					$err_msg = "GROUP BY can be set after WHERE|OR|AND";
				}
				break;
			case(QBStep::HAVING):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value === QBStep::GROUP_BY->value) ) {
					$order_error = true;
					$err_msg = "HAVING can be set directly after GROUP BY in a SELECT STATEMENT";
				}
				break;
			case(QBStep::ORDER_BY):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::HAVING->value) ) {
					$order_error = true;
					$err_msg = "ORDER BY can be set after FROM -> HAVING in a SELECT STATEMENT";
				} else {
					$this->order_is_set = true;
				}
				break;
			case(QBStep::LIMIT):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::ORDER_BY->value) ) {
					$order_error = true;
					$err_msg = "LIMIT can be set after FROM -> ORDER BY";
				} elseif( $this->db_type->value === DbType::MsSQL->value && !$this->order_is_set ) {
					$err_msg = "In MsSQL for a LIMIT an ORDER BY must be set";
					$order_error = true;
				} elseif( $this->db_type->value === DbType::Postgres->value && $this->query_type->value !== QueryType::SELECT->value ) {
					$err_msg = "In Postgres LIMIT is only allowed in a SELECT statement";
					$order_error = true;
				}
				break;
			case(QBStep::RETURNING):
				if( $this->db_type->value !== DbType::Postgres->value ) {
					$err_msg = "RETURNING can be used in Postgres only";
					$order_error = true;
				} elseif( $this->query_type->value === QueryType::SELECT->value ) {
					$err_msg = "RETURNING can not be used in a SELECT|TRUNCATE statement";
					$order_error = true;
				} elseif( ($this->query_type->value === QueryType::INSERT->value || $this->query_type->value === QueryType::UPDATE->value) && !($this->curr_step->value >= QBStep::VALUES->value) ) {
					$err_msg = "RETURNING can only be used after VALUES in a INSERT|UPDATE statement";
					$order_error = true;
				} elseif( $this->query_type->value === QueryType::DELETE->value && !($this->curr_step->value >= QBStep::FROM->value) ) {
					$err_msg = "RETURNING can only be used after FROM in a DELETE statement";
					$order_error = true;
				}
				break;
			default:
				throw new SystemException(__FILE__, __LINE__, "unknown query step.");
		}
		if( $order_error ) {
			throw new SystemException(__FILE__, __LINE__, "Query order error: " . $err_msg);
		}
		if( $step->value >= QBStep::NONE->value ) {
			$this->curr_step = $step;
		}
	}

}
#end