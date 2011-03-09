<?php
require_once 'db.mock.php';
/**
 * @property-read array $tables The TABLEs that are part of the query
 * @property-read array $columns The COLUMNs that are part of the query
 * @property-read array $wheres The WHERE conditions that are part of the query
 * @property-read array $joins The JOIN conditions that are part of the query
 * @property-read array $havings The HAVING conditions that are part of the query
 * @property-read array $group_bys The GROUP BY conditions that are part of the query
 * @property-read array $order_bys The ORDER BY conditions that are part of the query
 * @property-read int $limit The LIMIT condition that is part of the query
 * @property-read int $offset The OFFSET condition that is part of the query
 */
class query{
	/**
	 * The TABLEs that are part of the query
	 * @var array
	 */
	protected $tables = array();
	/**
	 * The COLUMNs that are part of the query
	 * @var array
	 */
	protected $columns = array();
	/**
	 * The WHERE conditions that are part of the query
	 * @var array
	 */
	protected $wheres = array();
	/**
	 * The JOIN conditions that are part of the query
	 * @var array
	 */
	protected $joins = array();
	/**
	 * The HAVING conditions that are being used in this query
	 * @var array
	 */
	protected $havings = array();
	/**
	 * The GROUP BY conditions that are part of the query
	 * @var array
	 */
	protected $group_bys = array();
	/**
	 * The ORDER BY conditions that are part of the query
	 * @var array
	 */
	protected $order_bys = array();
	/**
	 * The LIMIT condition that is part of the query
	 * @var int
	 */
	protected $limit = null;
	/**
	 * The OFFSET condition that is part of the query
	 * @var int
	 */
	protected $offset = null;
	/**
	 *
	 * @var database_class
	 */
	private $db = null;
	public function __construct(){
		$this->db = new db();
	}
	/**
	 * It is a get function, it returns things
	 * @param string $var
	 * @return mixed
	 */
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
	 * Add a join to the query
	 * @param string $table
	 * @param string $conditions
	 * @return query
	 */
	public function join($table, $conditions){
		$this->push_join($table, $conditions, 'JOIN');
		return $this;
	}
	/**
	 * Add a right join to the query
	 * @param string $table
	 * @param string $conditions
	 * @return query
	 */
	public function right_join($table, $conditions){
		$this->push_join($table, $conditions, 'RIGHT JOIN');
		return $this;
	}
	/**
	 * Add a left join to the query
	 * @param string $table
	 * @param string $conditions
	 * @return query
	 */
	public function left_join($table, $conditions){
		$this->push_join($table, $conditions, 'LEFT JOIN');
		return $this;
	}
	/**
	 * Add a straight join to the query
	 * @param string $table
	 * @param string $conditions
	 * @return query
	 */
	public function straight_join($table, $conditions){
		$this->push_join($table, $conditions, 'STRAIGHT JOIN');
		return $this;
	}
	/**
	 * Add an inner join to the query
	 * @param string $table
	 * @param string $conditions
	 * @return query
	 */
	public function inner_join($table, $conditions){
		$this->push_join($table, $conditions, 'INNER JOIN');
		return $this;
	}
	/**
	 * Add a cross join to the query
	 * @param string $table
	 * @param string $conditions
	 * @return query
	 */
	public function cross_join($table, $conditions){
		$this->push_join($table, $conditions, 'CROSS JOIN');
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
	 * Build a select string from the current query
	 * @return string
	 */
	public function build_select(){
		$select = 'SELECT '. $this->build_column_string()
				.' FROM ' . $this->build_table_string()
				. $this->build_join_string()
				. $this->build_where_string()
				. $this->build_group_by_string()
				. $this->build_order_by_string()
				. $this->build_having_string()
				. $this->build_limit_string()
				. $this->build_offset_string();
		return $select;
	}
	/**
	 * Add a GROUP BY to the stack
	 * @param string $filter The field to group by
	 * @param string $order The order
	 * @return query
	 */
	public function group_by($filter){
		$this->group_bys[] = array(
			'filter'=>$filter
		);
		return $this;
	}
	/**
	 * Clear the GROUP BY stack
	 * @return query
	 */
	public function clear_group_by(){
		$this->group_bys = array();
		return $this;
	}
	/**
	 * Add an ORDER BY to the stack
	 * @param string $column The column to order by
	 * @param string $order The order to sort by
	 * @return query
	 */
	public function order_by($column, $order = 'ASC'){
		$this->order_bys[] = array(
			'column'=>$column,
			'order'=>$order,
		);
		return $this;
	}
	/**
	 * Clear the ORDER BY stack
	 * @return query
	 */
	public function clear_order_by(){
		$this->order_bys = array();
		return $this;
	}
	/**
	 * Add a having to the having stack, clears out the having stack
	 * @param mixed $column The column being compared
	 * @param mixed $having The value being compared to
	 * @param string $comparison The comparison being done
	 * @param string $comparison_type Whether it is an and or an OR
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function having($column, $having, $comparison='=', $comparison_type='AND', $escape=true){
		$this->clear_havings();
		$this->push_having($column, $having, $comparison, $comparison_type, $escape);
		return $this;
	}
	/**
	 * Add a having to the having stack
	 * @param mixed $column The column being compared
	 * @param mixed $having The value being compared to
	 * @param string $comparison The comparison being done
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function or_having($column, $having, $comparison='=', $escape=true){
		$this->push_having($column, $having, $comparison, 'OR', $escape);
		return $this;
	}
	/**
	 * Add a having to the having stack
	 * @param mixed $column The column being compared
	 * @param mixed $having The value being compared to
	 * @param string $comparison The comparison being done
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function and_having($column, $having, $comparison='=', $escape=true){
		$this->push_having($column, $having, $comparison, 'AND', $escape);
		return $this;
	}
	/**
	 * Add a having to the conditions, clears out the where stack
	 * @param mixed $column The column being compared
	 * @param mixed $having The value being compared to
	 * @param string $comparison The comparison being done
	 * @param string $comparison_type Whether it is an and or an OR
	 * @param boolean $escape whether or not this value will be escaped
	 * @return query
	 */
	public function clear_havings(){
		$this->havings = array();
		return $this;
	}
	/**
	 * Add a having to the conditions, clears out the where stack
	 * @param mixed $column The column being compared
	 * @param mixed $having The value being compared to
	 * @param string $comparison The comparison being done
	 * @param string $comparison_type Whether it is an and or an OR
	 * @param boolean $escape whether or not this value will be escaped
	 */
	private function push_having($column, $having, $comparison='=', $comparison_type='AND', $escape=true){
		$this->havings[] = array(
			'column'=>$column,
			'having'=>$having,
			'comparison'=>$comparison,
			'comparison_type'=>$comparison_type,
			'escape'=>$escape
		);
	}
	/**
	 * Push a join onto the join stack
	 * @param string $table
	 * @param string $conditions
	 * @param string $type
	 */
	private function push_join($table, $conditions, $type){
		$this->joins[] = array(
			'table'=>$table,
			'conditions'=>$conditions,
			'type'=>$type,
		);
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
	 * Clear the where stack
	 * @return query
	 */
	public function clear_wheres(){
		$this->wheres = array();
		return $this;
	}
	/**
	 * Set the LIMIT for the query
	 * @param int $limit
	 * @return query
	 */
	public function set_limit($limit){
		$new_limit = (int)$limit;
		if($new_limit == $limit){
			$this->limit = $new_limit;
		} else {
			$this->clear_limit();
		}
		return $this;
	}
	/**
	 * Clear the LIMIT on the query
	 * @return query
	 */
	public function clear_limit(){
		$this->limit = null;
		return $this;
	}
	/**
	 * Set the LIMIT for the query
	 * @param int $offset
	 * @return query
	 */
	public function set_offset($offset){
		$new_offset = (int)$offset;
		if($new_offset == $offset){
			$this->offset = $new_offset;
		} else {
			$this->clear_offset();
		}
		return $this;
	}
	/**
	 * Clear the LIMIT on the query
	 * @return query
	 */
	public function clear_offset(){
		$this->offset = null;
		return $this;
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
	 * Push a closing bracket into the where stack to end the grouping of conditions
	 * @return query
	 */
	private function closed_bracket(){
		$this->wheres[] = array(
			'bracket'=>'CLOSE',
		);
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
				$string = ' WHERE ';
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
					$string .= db::QUOTE . $this->db->escape($w['value']) . db::QUOTE;
				} else {
					$string .= $w['value'];
				}
			}
		}
		return $string;
	}
	/**
	 * Build the JOIN string part of the query
	 * @return string
	 */
	private function build_join_string(){
		$string = '';
		foreach($this->joins as $j){
			$string .= ' ' . $j['type'] . ' ' . $j['table'] . ' ON (' . $j['conditions'] . ')';
		}
		return $string;
	}
	/**
	 * Build the having part of the query
	 * @return string
	 */
	private function build_having_string(){
		$string = '';
		if(!empty($this->havings)){
			$tmp = array_shift($this->havings);
			$string .= ' HAVING ' . $tmp['column'] . ' ' . $tmp['comparison'] . ' ' . db::QUOTE . $this->db->escape($tmp['having']) . db::QUOTE;
		}
		foreach($this->havings as $h){
			$string .= ' '.$h['comparison_type'] . ' ' . $h['column'] . ' ' . $h['comparison'] . ' ' . db::QUOTE . $this->db->escape($h['having']) . db::QUOTE;
		}
		return $string;
	}
	/**
	 * Build the GROUP BY part of the query
	 * @return $string
	 */
	private function build_group_by_string(){
		$string = '';
		if(!empty($this->group_bys)){
			$string .= ' GROUP BY ';
			$tmp = array();
			foreach($this->group_bys as $g){
				$tmp[] = $g['filter'];
			}
			$string .= implode(', ', $tmp);
		}
		return $string;
	}
	/**
	 * Build the ORDER BY part of the query
	 * @return $string
	 */
	private function build_order_by_string(){
		$string = '';
		if(!empty($this->order_bys)){
			$string .= ' ORDER BY ';
			$tmp = array();
			foreach($this->order_bys as $o){
				$tmp[] = $o['column'] . ' ' . $o['order'];
			}
			$string .= implode(', ', $tmp);
		}
		return $string;
	}
	/**
	 * Build the LIMIT part of the query
	 * @return string
	 */
	private function build_limit_string(){
		$string = '';
		if($this->limit){
			$string .= ' LIMIT ' . $this->limit;
		}
		return $string;
	}
	/**
	 * Build the OFFSET part of the query
	 * @return string
	 */
	private function build_offset_string(){
		$string = '';
		if($this->offset){
			$string .= ' OFFSET ' . $this->offset;
		}
		return $string;
	}
}