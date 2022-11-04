<?php
namespace ITRocks\Framework\View;

/**
 * An interface for your business objects having an objectClass() method to return a class for
 * object display (row, form, etc.)
 */
interface Has_Object_Class
{

	//----------------------------------------------------------------------------------- objectClass
	/**
	 * @return string
	 */
	public function objectClass() : string;

}
