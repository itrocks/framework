<?php
namespace SAF\Framework;

use SAF\AOP\Around_Method_Joinpoint;
use SAF\Plugins;

/**
 * This plugin enables storage of properties displayed into lists as acls
 */
class Acls_List_Properties extends Acls_Properties implements Plugins\Registerable
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
	public function listControllerGetProperties(
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
	public function propertyAddController(
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
	public function propertyRemoveController(
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
	/**
	 *
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->aroundMethod(
			array('SAF\Framework\Default_List_Controller', "getPropertiesList"),
			array($this, "listControllerGetProperties")
		);
		$aop->aroundMethod(
			array('SAF\Framework\Property_Add_Controller', "run"),
			array($this, "propertyAddController")
		);
		$aop->aroundMethod(
			array('SAF\Framework\Property_Remove_Controller', "run"),
			array($this, "propertyRemoveController")
		);
	}

}
