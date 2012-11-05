<?php
namespace SAF\Framework;

class Default_List_Controller implements List_Controller
{

	//----------------------------------------------------------------------------------- $class_name
	private $class_name;

	//----------------------------------------------------------------------------- getListProperties
	public function getListProperties()
	{
		return Default_List_Controller_Configuration::current()->getListProperties($this->class_name);
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
		$this->class_name = Set::elementClassNameOf($class_name);
		$list = Dao::select($this->class_name, $this->getListProperties());
		$parameters = array_merge(array($this->class_name => $list), $parameters);
		View::run($parameters, $form, $files, $class_name, "list");
	}

}
