<?php
namespace SAF\Framework;
use AopJoinpoint;

class Output_Controller_Acls implements Plugin
{

	//----------------------------------------------------------------------------- getPropertiesList
	public static function getPropertiesList($class_name)
	{
		$acls = Acls::current();
		$list = isset($acls)
			? $acls->get($class_name . ".output.properties.list")
			: null;
echo "<pre>" . print_r($list, true) . "</pre>";
		return isset($list) ? array_keys($list) : null;
	}

	//----------------------------------------------------------- onOutputControllerGetPropertiesList
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onOutputControllerGetPropertiesList(AopJoinpoint $joinpoint)
	{
		$result = self::getPropertiesList($joinpoint->getArguments()[0]);
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
			__NAMESPACE__ . "\\Default_Output_Controller->getPropertiesList()",
			array(__CLASS__, "onOutputControllerGetPropertiesList")
		);
	}

}
