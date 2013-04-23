<?php
namespace SAF\Framework;
use AopJoinpoint;

class Acls_List_Properties extends Acls_Properties implements Plugin
{

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * @return string[]
	 */
	public function getDefaultProperties()
	{
		return Reflection_Class::getInstanceOf($this->context_class_name)
			->getListAnnotation("representative")->values();
	}

	//------------------------------------------------------------------- listControllerGetProperties
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function listControllerGetProperties(AopJoinpoint $joinpoint)
	{
		$acls_list_properties = new Acls_List_Properties($joinpoint->getArguments()[0]);
		$properties = $acls_list_properties->getPropertiesNames("list");
		if (isset($properties)) {
			$joinpoint->setReturnedValue($properties);
		}
		else {
			$joinpoint->process();
		}
	}

	//------------------------------------------------------------------------- propertyAddController
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function propertyAddController(AopJoinpoint $joinpoint)
	{
		/**
		 * @var $parameters Controller_Parameters
		 * - key 0 : context class name (ie a business object)
		 * - key 1 : context feature name (ie "output", "list")
		 * - keys 2 and more : the identifiers of the removed elements (ie property names)
		 * @var $form  array  unused
		 * @var $files array  unused
		 */
		list($parameters, $form, $files) = $joinpoint->getArguments();
		if ($parameters->getRawParameter(1) == "list") {
			$parameters->unshiftUnnamed(__CLASS__);
			(new Acls_Property_Add_Controller())->run($parameters, $form, $files);
		}
		else {
			$joinpoint->process();
		}
	}

	//---------------------------------------------------------------------- propertyRemoveController
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function propertyRemoveController(AopJoinpoint $joinpoint)
	{
		/**
		 * @var $parameters Controller_Parameters
		 * - key 0 : context class name (ie a business object)
		 * - key 1 : context feature name (ie "output", "list")
		 * - keys 2 and more : the identifiers of the removed elements (ie property names)
		 * @var $form  array  unused
		 * @var $files array  unused
		 */
		list($parameters, $form, $files) = $joinpoint->getArguments();
		if ($parameters->getRawParameter(1) == "list") {
			$parameters->unshiftUnnamed(__CLASS__);
			(new Acls_Property_Remove_Controller())->run($parameters, $form, $files);
		}
		else {
			$joinpoint->process();
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add(Aop::AROUND,
			'SAF\Framework\Default_List_Controller->getPropertiesList()',
			array(__CLASS__, "listControllerGetProperties")
		);
		Aop::add(Aop::AROUND,
			'SAF\Framework\Property_Add_Controller->run()',
			array(__CLASS__, "propertyAddController")
		);
		Aop::add(Aop::AROUND,
			'SAF\Framework\Property_Remove_Controller->run()',
			array(__CLASS__, "propertyRemoveController")
		);
	}

}
