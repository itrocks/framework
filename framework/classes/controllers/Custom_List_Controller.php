<?php
namespace SAF\Framework;

class Custom_List_Controller implements Class_Controller
{

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @getter getProperties
	 * @var multitype:string
	 */
	public $properties; 

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public function getProperties(AopJoinpoint $joinpoint)
	{
		if (MLocks::lock($this, __METHOD__)) return;
		if (!isset($this->properties)) {
		}
		$joinpoint->setReturnedValue($this->properties);
		MLocks::unlock($this, __METHOD__);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "list-typed" controller
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $parameters->getObjects();
		$element_class_name = Set::elementClassNameOf($class_name);
		$set = Set::instantiate($class_name, Dao::readAll($element_class_name));
		$parameters = array_merge(array($class_name => $set), $parameters);
		View::run($parameters, $form, $files, $class_name, "list");
	}

}
