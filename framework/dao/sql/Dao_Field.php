<?php

interface Dao_Field
{

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 * @values float, integer, string, Date_Time
	 */
	//--------------------------------------------------------------------------------------- getType
	public function getType();

}
