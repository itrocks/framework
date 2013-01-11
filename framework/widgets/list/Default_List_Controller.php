<?php
namespace SAF\Framework;

class Default_List_Controller extends List_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	protected function getGeneralButtons($class_name)
	{
		return array(
			new Button("Add", View::link($class_name, "new"), "add")
		);
	}

	//----------------------------------------------------------------------------- getPropertiesList
	protected function getPropertiesList($class_name)
	{
		return Reflection_Class::getInstanceOf($class_name)->getAnnotation("representative")->value;
	}

	//--------------------------------------------------------------------------- getSelectionButtons
	protected function getSelectionButtons($class_name)
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
		$element_class_name = Set::elementClassNameOf($class_name);
		$parameters = array_merge(
			array($element_class_name => Dao::select(
				$element_class_name, $this->getPropertiesList($element_class_name)
			)),
			$parameters
		);
		$parameters["general_buttons"]   = $this->getGeneralButtons($element_class_name);
		$parameters["selection_buttons"] = $this->getSelectionButtons($element_class_name);
		View::run($parameters, $form, $files, $class_name, "list");
	}

}
