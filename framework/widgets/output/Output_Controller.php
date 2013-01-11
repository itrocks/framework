<?php
namespace SAF\Framework;

abstract class Output_Controller implements Default_Feature_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param string $class_name
	 * @return multitype:Button
	 */
	protected function getGeneralButtons($class_name)
	{
		return array();
	}

	//----------------------------------------------------------------------------- getPropertiesList
	/**
	 * @param string $class_name
	 * @return multitype:string property names list
	 */
	protected function getPropertiesList($class_name)
	{
		return null;
	}

}
