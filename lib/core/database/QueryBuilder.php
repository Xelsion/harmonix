<?php

namespace lib\core\database;

use lib\core\enums\DbType;
use lib\core\enums\QBStep;
use lib\core\enums\QueryType;
use lib\core\exceptions\SystemException;
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
	private bool $required_order = false;
	private bool $order_is_set = false;

	private int $param_counter = 0;

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
		if( is_null($columns) ) {
			$this->sql = "SELECT *";
		} elseif( is_array($columns) ) {
			$this->sql = "SELECT " . implode(", ", $columns);
		} else {
			$this->sql = "SELECT " . $columns;
		}
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
		if( is_array($tables) ) {
			$this->sql .= " FROM " . implode(", ", $tables);
		} else {
			$this->sql .= " FROM " . $tables;
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
		return $this;
	}

	/**
	 * @param string $alias
	 * @return $this
	 * @throws SystemException
	 */
	public function As(string $alias): static {
		$this->sql .= " AS " . $alias;
		return $this;
	}

	// ==================== Where ====================

	/**
	 * @param array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function Where(array $conditions): static {
		$this->checkSQL(QBStep::WHERE);
		$this->where_clauses[] = ['logic' => 'AND', 'data' => $conditions];
		return $this;
	}

	/**
	 * @param array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function And(array $conditions): static {
		$this->checkSQL(QBStep::WHERE_ADDS);
		$this->where_clauses[] = ['logic' => 'AND', 'data' => $conditions];
		return $this;
	}

	/**
	 * @param array $conditions
	 * @return $this
	 * @throws SystemException
	 */
	public function Or(array $conditions): static {
		$this->checkSQL(QBStep::WHERE_ADDS);
		$this->where_clauses[] = ['logic' => 'OR', 'data' => $conditions];
		return $this;
	}

	/**
	 * @return string
	 */
	protected function compileFinalWhere(): string {
		if( empty($this->where_clauses) ) {
			return "";
		}
		$finalParts = [];
		foreach( $this->where_clauses as $index => $clause ) {
			$compiled = $this->compileConditions($clause['data']);
			if( $compiled !== "" ) {
				$prefix = ($index === 0) ? "" : $clause['logic'] . " ";
				$finalParts[] = $prefix . "(" . $compiled . ")";
			}
		}
		return " WHERE " . implode(" ", $finalParts);
	}

	/**
	 * @param array $conditions
	 * @param string $logic
	 * @return string
	 */
	protected function compileConditions(array $conditions, string $logic = 'AND'): string {
		$parts = [];
		foreach( $conditions as $column => $value ) {
			if( in_array(strtoupper($column), ['AND', 'OR']) ) {
				$parts[] = "(" . $this->compileConditions($value, strtoupper($column)) . ")";
				continue;
			}

			$placeholder = $this->makePlaceholderName($column);

			if( is_array($value) ) {
				$operator = strtoupper((string)key($value));
				$val = current($value);
				switch( $operator ) {
					case 'BETWEEN':
					case 'NOT BETWEEN':
						$p1 = $placeholder . "_a";
						$p2 = $placeholder . "_b";
						$parts[] = "{$column} {$operator} :{$p1} AND :{$p2}";
						$this->addBindParam($p1, $val[0]);
						$this->addBindParam($p2, $val[1]);
						break;
					case 'IN':
					case 'NOT IN':
						$vals = (array)$val;
						if( count($vals) === 0 ) {
							$parts[] = ($operator === 'IN') ? '1=0' : '1=1';
							break;
						}
						$inPlaceholders = [];
						foreach( (array)$val as $i => $v ) {
							$p = $placeholder . "_" . $i;
							$inPlaceholders[] = $p;
							$this->addBindParam($p, $v);
						}
						$parts[] = "{$column} {$operator} (:" . implode(', :', $inPlaceholders) . ")";
						break;
					case 'IS':
					case 'IS NOT':
						$parts[] = "{$column} {$operator} NULL";
						break;
					case 'EXISTS':
					case 'NOT EXISTS':
						$subquery = ($val instanceof self) ? $val->sql : $val;
						$parts[] = "{$operator} ({$subquery})";
						break;
					case 'RAW':
						$parts[] = "{$column} {$val}";
						break;
					default:
						$parts[] = "{$column} {$operator} :{$placeholder}";
						$this->addBindParam($placeholder, $val);
						break;
				}
			} else {
				$parts[] = is_null($value) ? "{$column} IS NULL" : "{$column} = :{$placeholder}";
				if( !is_null($value) ) {
					$this->addBindParam($placeholder, $value);
				}
			}
		}
		return implode(" {$logic} ", $parts);
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
				$this->sql .= " SET " . implode(", ", array_map(fn($c) => "$c=:$c", $cols));
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
	 * @param array $options
	 * @return $this
	 * @throws SystemException
	 */
	public function prepareStatement(array $options = []): static {
		if( $this->required_order && !$this->order_is_set ) {
			throw new SystemException(__FILE__, __LINE__, "MSSql requires an order if a limit is set.");
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
	 * @return void
	 * @throws SystemException
	 */
	private function checkSQL(QBStep $step): void {
		$order_error = false;
		switch( $step ) {
			case(QBStep::START):
				if( $this->curr_step->value !== QBStep::NONE->value ) {
					$order_error = true;
				}
				break;
			case(QBStep::VALUES):
				if( ($this->query_type->value === QueryType::INSERT->value || $this->query_type->value === QueryType::UPDATE->value) && $this->curr_step->value !== QBStep::START->value ) {
					$order_error = true;
				}
				break;
			case(QBStep::FROM):
				if( ($this->query_type->value === QueryType::SELECT->value || $this->query_type->value === QueryType::DELETE->value) && $this->curr_step->value !== QBStep::START->value ) {
					$order_error = true;
				}
				break;
			case(QBStep::JOIN):
				if( $this->curr_step->value !== QBStep::FROM->value ) {
					$order_error = true;
				}
				break;
			case(QBStep::WHERE):
				if( ($this->query_type->value === QueryType::SELECT->value || $this->query_type->value === QueryType::DELETE->value) && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$order_error = true;
				} else if( $this->query_type->value === QueryType::UPDATE->value && !($this->curr_step->value >= QBStep::VALUES->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$order_error = true;
				}
				break;
			case(QBStep::WHERE_ADDS):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::WHERE->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$order_error = true;
				}
				break;
			case(QBStep::GROUP_BY):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::WHERE_ADDS->value) ) {
					$order_error = true;
				}
				break;
			case(QBStep::HAVING):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::GROUP_BY->value) ) {
					$order_error = true;
				}
				break;
			case(QBStep::ORDER_BY):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::HAVING->value) ) {
					$order_error = true;
				}
				$this->order_is_set = true;
				break;
			case(QBStep::LIMIT):
				if( $this->query_type->value === QueryType::SELECT->value && !($this->curr_step->value >= QBStep::FROM->value && $this->curr_step->value <= QBStep::ORDER_BY->value) ) {
					$order_error = true;
				} elseif( $this->db_type->value === DbType::MsSQL->value && !$this->order_is_set ) {
					$order_error = true;
				}
				break;
			default:
				throw new SystemException(__FILE__, __LINE__, "unknown query step.");
		}
		if( $order_error ) {
			throw new SystemException(__FILE__, __LINE__, "Query order error.");
		}
		$this->curr_step = $step;
	}

}
#end