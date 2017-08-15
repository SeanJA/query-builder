<?php

interface queryBuilderDbInterface{
	/**
	 * escapes value
	 * @param $value
	 * @return mixed
	 */
	public function escape($value);

	/**
	 * return quotes to be used in query
	 * @return mixed
	 */
	public function quote();

	/**
	 * return field quotes to be used in query
	 * @return mixed
	 */
	public function field_quote();
}
