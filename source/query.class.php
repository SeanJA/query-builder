<?php
/**
 * @property-read array $tables The tables that are part of the query
 * @property-read array $columns The columns that are part of the query
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
	 * @param string $column the column being added
	 * @param string $alias the alias of this table
	 * @return query
	 */
	public function column($column, $alias=null){
		$this->columns[] = array(
			'column'=>$column,
			'alias'=>$alias
		);
		return $this;
	}
}