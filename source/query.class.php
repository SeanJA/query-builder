<?php
require_once 'db.mock.php';
/**
 * @property-read array $tables The tables that are part of the query
 * @property-read array $columns The columns that are part of the query
 * @property-read array $wheres The where conditions that are part of the query
 */
class query{
	const QUOTE = "'";
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
	/**
	 *
	 * @var database_class
	 */
	private $db = null;
	public function __construct(){
		$this->db = new db_mock();
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
	 * @param mixed $value The value being compared to
	 * @param string $comparison The comparison being done
	 * @param string $comparison_type Whether it is an AND or an OR
	 * @param boolean $escape whether or not this value will be escaped
	 */
	private function push_where($column, $value, $comparison, $comparison_type, $escape){
		$comparison = strtoupper($comparison);
		$comparison_type = strtoupper($comparison_type);
		$old_column = $column;
		$column = $this->filter_column($column);
		$old_value = $value;
		$value = $this->filter_column($value);
		if($old_value !== $value){
			$escape = false;
		}
		$this->wheres[] = array(
			'column'=>$column,
			'value'=>$value,
			'comparison'=>$comparison,
			'type'=>$comparison_type,
			'escape'=>$escape,
		);
	}
	/**
	 * Add an 'AND' to the conditions
	 * @param mixed $column The column being compared
	 * @param mixed $value The value being compared to
	 * @param string $comparison The comparison being done
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function and_where($column, $value, $comparison='=', $escape= true){
		$this->push_where($column, $value, $comparison, 'AND', $escape);
		return $this;
	}
	/**
	 * Add an 'AND' to the conditions
	 * @param mixed $column The column being compared
	 * @param mixed $value The value being compared to
	 * @param string $comparison The comparison being done
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function or_where($column, $value, $comparison='=', $escape= true){
		$this->push_where($column, $value, $comparison, 'OR', $escape);
		return $this;
	}
	/**
	 * Push an open bracket into the where stack to group OR conditions
	 * @return query
	 */
	public function begin_or(){
		$this->wheres[] = array(
			'bracket'=>'OPEN',
			'type'=>'OR'
		);
		return $this;
	}
	/**
	 * Push an open bracket into the where stack to group AND conditions
	 * @return query
	 */
	public function begin_and(){
		$this->wheres[] = array(
			'bracket'=>'OPEN',
			'type'=>'AND'
		);
		return $this;
	}
	/**
	 * End an OR condition group
	 * @return query
	 */
	public function end_or(){
		$this->closed_bracket();
		return $this;
	}
	/**
	 * End an AND condition group
	 * @return query
	 */
	public function end_and(){
		$this->closed_bracket();
		return $this;
	}
	/**
	 * Push a closing bracket into the where stack to end the grouping of conditions
	 * @return query
	 */
	private function closed_bracket(){
		$this->wheres[] = array(
			'bracket'=>'CLOSE',
		);
	}
	/**
	 * Build a select string from the current query
	 * @return string
	 */
	public function build_select(){
		$select = 'SELECT '. $this->build_column_string()
				.' FROM ' . $this->build_table_string()
				.' ' . $this->build_where_string();
		return $select;
	}
	/**
	 * Build the COLUMN string part of the query
	 * @return string
	 */
	private function build_column_string(){
		$columns = $this->build_alias_array($this->columns, 'column');
		$string = implode(', ',$columns);
		if(!$string){
			$string = '*';
		}
		return $string;
	}
	/**
	 * Build the TABLE string part of the query
	 * @return string
	 */
	private function build_table_string(){
		$tables = $this->build_alias_array($this->tables, 'table');
		$string = implode(', ',$tables);
		return $string;
	}
	/**
	 * Build an alias array out of the array
	 * @param array $array
	 * @param string $default the value that is always there
	 * @return string
	 */
	private function build_alias_array(array $array, $default){
		$return = array();
		foreach($array as $a){
			$temp = $a[$default];
			if($a['alias']){
				$temp .= ' AS ' . $a['alias'];
			}
			$return[] = $temp;
		}
		return $return;
	}
	/**
	 * Build the WHERE string part of the query
	 * @return string
	 */
	private function build_where_string(){
		$first = true;
		$bracket = false;
		foreach($this->wheres as $w){
			if($first){
				$string = 'WHERE ';
				$first = false;
			} else {
				if(!$bracket && !isset($w['bracket'])){
					$string .= ' ' . $w['type'] . ' ';
				} else {
					$bracket = false;
				}
			}
			if(isset($w['bracket'])){
				if($w['bracket'] === 'OPEN'){
					$string .= '( ';
					$bracket = true;
				} else {
					$string .= ' )';
				}
			} else {
				$string .= $w['column'] . ' ' . $w['comparison']. ' ';
				if($w['escape']){
					$string .= self::QUOTE . $this->db->escape($w['value']) . self::QUOTE;
				} else {
					$string .= $w['value'];
				}
			}
		}
		return $string;
	}
}