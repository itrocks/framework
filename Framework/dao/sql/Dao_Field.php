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
	 * @values float, integer, string, DateTime
	 */
	//--------------------------------------------------------------------------------------- getType
	public function getType();

}
