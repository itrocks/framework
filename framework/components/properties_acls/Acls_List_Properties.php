<?php
namespace SAF\Framework;

/**
 * This plugin enables storage of properties displayed into lists as acls
 */
class Acls_List_Properties extends Acls_Properties implements Plugin
{

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * @return string[]
	 */
	public function getDefaultProperties()
	{
		return (new Reflection_Class($this->context_class_name))
			->getListAnnotation("representative")->values();
	}

	//------------------------------------------------------------------- listControllerGetProperties
	/**
	 * @param $class_name string
	 * @param $joinpoint Around_Method_Joinpoint
	 * @return string[] property names list
	 */
	public static function listControllerGetProperties(
		$class_name, Around_Method_Joinpoint $joinpoint
	) {
		$acls_list_properties = new Acls_List_Properties($class_name);
		$properties = $acls_list_properties->getPropertiesNames("list");
		return (isset($properties)) ? $properties : $joinpoint->process($class_name);
	}

	//------------------------------------------------------------------------- propertyAddController
	/**
	 * @param $parameters Controller_Parameters removal parameters
	 * - key 0 : context class name (ie a business class)
	 * - key 1 : context feature name (ie "output", "list")
	 * - keys 2 and more : the identifiers of the removed elements (ie property names)
	 * @param $form       array not used
	 * @param $files      array not used
	 * @param $joinpoint  Around_Method_Joinpoint
	 * @return mixed
	 */
	public static function propertyAddController(
		Controller_Parameters $parameters, $form, $files, Around_Method_Joinpoint $joinpoint
	) {
		if ($parameters->getRawParameter(1) == "list") {
			$parameters->unshiftUnnamed(__CLASS__);
			return (new Acls_Property_Add_Controller)->run($parameters, $form, $files);
		}
		else {
			return $joinpoint->process($parameters, $form, $files);
		}
	}

	//---------------------------------------------------------------------- propertyRemoveController
	/**
	 * Call this to remove an element from a given class + feature context
	 *
	 * @param $parameters Controller_Parameters removal parameters
	 * - key 0 : context class name (ie a business class)
	 * - key 1 : context feature name (ie "output", "list")
	 * - keys 2 and more : the identifiers of the removed elements (ie property names)
	 * @param $form       array not used
	 * @param $files      array not used
	 * @param $joinpoint  Around_Method_Joinpoint
	 * @return mixed
	 */
	public static function propertyRemoveController(
		Controller_Parameters $parameters, $form, $files, Around_Method_Joinpoint $joinpoint
	) {
		if ($parameters->getRawParameter(1) == "list") {
			$parameters->unshiftUnnamed(__CLASS__);
			return (new Acls_Property_Remove_Controller)->run($parameters, $form, $files);
		}
		else {
			return $joinpoint->process($parameters, $form, $files);
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::addAroundMethodCall(
			array('SAF\Framework\Default_List_Controller', "getPropertiesList"),
			array(__CLASS__, "listControllerGetProperties")
		);
		Aop::addAroundMethodCall(
			array('SAF\Framework\Property_Add_Controller', "run"),
			array(__CLASS__, "propertyAddController")
		);
		Aop::addAroundMethodCall(
			array('SAF\Framework\Property_Remove_Controller', "run"),
			array(__CLASS__, "propertyRemoveController")
		);
	}

}
