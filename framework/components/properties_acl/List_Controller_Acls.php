<?php
namespace SAF\Framework;
use AopJoinpoint;

class List_Controller_Acls implements Plugin
{

	//------------------------------------------------------------------------------- addListProperty
	/**
	 * Add list properties list from acls
	 * @param String $class_name
	 * @param String $property_name
	 */
	public static function addListProperty($class_name, $property_name)
	{
		$acls = Acls::current();
		$right = new Acl_Right();
		$right->key = $class_name . ".list.properties.list." . $property_name;
		$right->value = $property_name;
 		$right->group = Acls_User::current()->getUserGroup();
		$acls->add($right);
		Dao::write($right);
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
		$list = $acls->get($class_name . ".list.properties.list");
		return isset($list) ? array_keys($list) : null;
	}

	//------------------------------------------------ onListControllerConfigurationGetListProperties
	/**
	 * @param AopJoinpoint $joinpoint
	 * @return 
	 */
	public static function onListControllerGetListProperties(AopJoinpoint $joinpoint)
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
		Aop::add("around",
			__NAMESPACE__ . "\\Default_List_Controller->getListProperties()",
			array(__CLASS__, "onListControllerGetListProperties")
		);
	}

	//---------------------------------------------------------------------------- removeListProperty
	public static function removeListProperty($class_name, $property_name)
	{
		$acls = Acls::current();
		
		$right = new Acl_Right();
		$right->key = $class_name . ".list.properties.list." . $property_name;
		$right->value = $property_name;
		$right->group = Acls_User::current()->getUserGroup();
		$acls->remove($class_name . ".list.properties.list." . $property_name);
		// TODO HCR : Appliquer la suppression dans la base de donnée
// 		$objects = Dao::search($right);
// 		foreach ($objects as $object)
// 			Dao::delete($object);
	}

}
