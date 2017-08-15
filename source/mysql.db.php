<?php
require_once dirname(__FILE__) . '/db.interface.php';

class mysqlQueryBuilderDb implements queryBuilderDbInterface{

	/** {@inheritDoc} */
	public function escape($value){
		return mysql_real_escape_string($value);
	}

	/** {@inheritDoc} */
	public function quote()
	{
		return "'";
	}

	/** {@inheritDoc} */
	public function field_quote()
	{
		return "`";
	}
}
