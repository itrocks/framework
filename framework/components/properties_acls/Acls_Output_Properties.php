<?php
namespace SAF\Framework;
use AopJoinpoint;

/**
 * This plugin enables storage of properties available into output / edit views as acls
 */
class Acls_Output_Properties extends Acls_Properties implements Plugin
{

	//----------------------------------------------------------------- outputControllerGetProperties
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function outputControllerGetProperties(AopJoinpoint $joinpoint)
	{
		$acls_output_properties = new Acls_Output_Properties($joinpoint->getArguments()[0]);
		$properties = $acls_output_properties->getPropertiesNames("output");
		if (isset($properties)) {
			$joinpoint->setReturnedValue($properties);
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
		 * - key 1 : context feature name (ie "output", "output")
		 * - keys 2 and more : the identifiers of the removed elements (ie property names)
		 * @var $form  array  unused
		 * @var $files array  unused
		 */
		list($parameters, $form, $files, ) = $joinpoint->getArguments();
		if ($parameters->getRawParameter(1) == "edit") {
			$parameters->set(1, "output");
		}
		if ($parameters->getRawParameter(1) == "output") {
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
			'SAF\Framework\Default_Output_Controller->getPropertiesList()',
			array(__CLASS__, "outputControllerGetProperties")
		);
		Aop::add(Aop::AROUND,
			'SAF\Framework\Property_Remove_Controller->run()',
			array(__CLASS__, "propertyRemoveController")
		);
	}

}
