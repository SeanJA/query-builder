<?php
/**
 * @property-read array $tables The tables that are part of the query
 * @property-read array $columns The columns that are part of the query
 * @property-read array $wheres The where conditions that are part of the query
 */
class query{
	/**
	 * The tables that are part of the query
	 * @var array
	 */
	protected $tables = array();
	/**
	 *  The columns that are part of the query
	 * @var array
	 */
	protected $columns = array();
	/**
	 * The where conditions that are part of the query
	 * @var array
	 */
	protected $wheres = array();
	public function __construct(){
		
	}
	public function __get($var){
		return $this->$var;
	}
	/**
	 * Add a table to the query
	 * @param string $table the table being added
	 * @param string $alias the alias of this table
	 * @return query
	 */
	public function table($table, $alias=null){
		$this->tables[] = array(
			'table'=>$table,
			'alias'=>$alias
		);
		return $this;
	}
	/**
	 * Add a column to the query
	 * @param mixed $column the column being added
	 * @param string $alias the alias of this table
	 * @return query
	 */
	public function column($column, $alias=null){
		$column = $this->filter_column($column);
		$this->columns[] = array(
			'column'=>$column,
			'alias'=>$alias
		);
		return $this;
	}
	/**
	 * Handle some special column value cases
	 * @param mixed $column
	 * @return string
	 */
	private function filter_column($column){
		if($column === null){
			$column = 'NULL';
		}elseif($column === true){
			$column = 'TRUE';
		}elseif($column === false){
			$column = 'FALSE';
		}
		return $column;
	}
	/**
	 * Clear the where stack
	 */
	private function clear_wheres(){
		$this->wheres = array();
	}
	/**
	 * Add a where to the conditions, clears out the where stack
	 * @param mixed $column The column being compared
	 * @param mixed $where The value being compared to
	 * @param string $comparison The comparison being done
	 * @param string $comparison_type Whether it is an and or an OR
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function where($column, $where, $comparison='=', $escape=true){
		$this->clear_wheres();
		$this->push_where($column, $where, $comparison, null, $escape);
		return $this;
	}
	/**
	 * Push another value onto the where stack
	 * @param mixed $column The column being compared
	 * @param mixed $where The value being compared to
	 * @param string $comparison The comparison being done
	 * @param string $comparison_type Whether it is an AND or an OR
	 * @param boolean $escape whether or not this value will be escaped
	 */
	private function push_where($column, $where, $comparison, $comparison_type, $escape){
		$comparison = strtoupper($comparison);
		$comparison_type = strtoupper($comparison_type);
		$column = $this->filter_column($column);
		$where = $this->filter_column($where);
		$this->wheres[] = array(
			'column'=>$column,
			'where'=>$where,
			'comparison'=>$comparison,
			'type'=>$comparison_type,
			'escape'=>$escape,
		);
	}
	/**
	 * Add an 'AND' to the conditions
	 * @param mixed $column The column being compared
	 * @param mixed $where The value being compared to
	 * @param string $comparison The comparison being done
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function and_where($column, $where, $comparison='=', $escape= true){
		$this->push_where($column, $where, $comparison, 'AND', $escape);
		return $this;
	}
	/**
	 * Add an 'AND' to the conditions
	 * @param mixed $column The column being compared
	 * @param mixed $where The value being compared to
	 * @param string $comparison The comparison being done
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function or_where($column, $where, $comparison='=', $escape= true){
		$this->push_where($column, $where, $comparison, 'OR', $escape);
		return $this;
	}
}