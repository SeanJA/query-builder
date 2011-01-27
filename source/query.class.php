<?php

class query{
	/**
	 * The tables that are part of the query
	 * @var array
	 */
	protected $tables = array();
	public function __construct(){
		
	}
	public function __get($var){
		return $this->$var;
	}
	public function table($table, $alias=null){
		if(!$alias){
			$alias = $table;
		}
		$this->tables[] = array(
			'table'=>$table,
			'alias'=>$alias
		);
		return $this;
	}
}