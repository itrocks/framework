<?php
namespace SAF\Framework;

class Default_Write_Controller implements Class_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default "write-typed" controller
	 *
	 * Save data from the posted form into the first parameter object using standard method.
	 * Create a new instance of this object if no identifier was given.
	 *
	 * @todo not implemented yet, please do something
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		echo "<pre>" . print_r($parameters, true) . "</pre>";
		echo "form = $form<br>";
		echo "files = $files<br>";
		echo "class_name = $class_name<br>";
	}

}
