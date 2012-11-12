<?php
namespace SAF\Framework;

class Default_List_Controller implements List_Controller
{

	//----------------------------------------------------------------------------------- $class_name
	private $class_name;

	//----------------------------------------------------------------------------- getGeneralButtons
	public function getGeneralButtons($class_name)
	{
		return array(
			new Button("Add", View::link($class_name, "new"), "add"),
		);
	}

	//----------------------------------------------------------------------------- getListProperties
	public function getListProperties()
	{
		return Default_List_Controller_Configuration::current()->getListProperties($this->class_name);
	}

	//--------------------------------------------------------------------------- getSelectionButtons
	public function getSelectionButtons($class_name)
	{
		return array(
			new Button("Print", View::link($class_name, "print"), "print")
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "list-typed" view controller
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
		$parameters["general_buttons"]   = $this->getGeneralButtons($this->class_name);
		$parameters["selection_buttons"] = $this->getSelectionButtons($this->class_name);
		View::run($parameters, $form, $files, $class_name, "list");
	}

}
