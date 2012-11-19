<?php
namespace SAF\Framework;
use AopJoinpoint;

class List_Controller_Acls
{

	//------------------------------------------------------------------------------- addListProperty
	public static function addListProperty($class_name, $property_name)
	{
		
	}

	//----------------------------------------------------------------------------- getListProperties
	/**
	 * Get list properties list from acls
	 *
	 * @param string $class_name
	 * @return multitype:string
	 */
	public static function getListProperties($class_name)
	{
		return null;
	}

	//-------------------------------------------------------------------------- getListPropertiesAop
	public static function getListPropertiesAop(AopJoinpoint $joinpoint)
	{
		$result = List_Controller_Acls::getListProperties($joinpoint->getArguments()[0]);
		return ($result == null) ? $joinpoint->process() : $result;
	}

	//---------------------------------------------------------------------------- removeListProperty
	public static function removeListProperty($class_name, $property_name)
	{
		
	}

}
