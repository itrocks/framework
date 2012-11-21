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
		$acls = Acls::current();
		return null;
	}

	//------------------------------------------------ onListControllerConfigurationGetListProperties
	/**
	 * @param AopJoinpoint $joinpoint
	 * @return 
	 */
	public static function onListControllerConfigurationGetListProperties(AopJoinpoint $joinpoint)
	{
		$result = self::getListProperties($joinpoint->getArguments()[0]);
		if (isset($result)) {
			$joinpoint->setReturnedValue($result);
		}
		else {
			$joinpoint->process();
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		aop_add_around(
			__NAMESPACE__ . "\\Default_List_Controller_Configuration->getListProperties()",
			array(__CLASS__, "onListControllerConfigurationGetListProperties")
		);
	}

	//---------------------------------------------------------------------------- removeListProperty
	public static function removeListProperty($class_name, $property_name)
	{
		
	}

}
