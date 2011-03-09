<?php

class db{
	const QUOTE = "'";
	const FIELD_QUOTE = "`";

	function escape($value){
		return $value;
	}
}
