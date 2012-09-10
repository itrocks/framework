<?php
namespace SAF\Framework;

interface Field
{

	//--------------------------------------------------------------------------------------- getName
	/**
	 * Get the field name
	 *
	 * @return string
	 */
	public function getName();

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Get the php data type for the field
	 *
	 * @return string
	 * @values float, integer, string, Date_Time, *
	 */
	public function getType();

}
