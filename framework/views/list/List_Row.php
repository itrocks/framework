<?php
namespace SAF\Framework;

interface List_Row
{

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns object associated to the list row
	 *
	 * @return object
	 */
	public function getObject();

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets a value from the row
	 *
	 * @param string $property the path of the property
	 * @return mixed 
	 */
	public function getValue($property);

}
