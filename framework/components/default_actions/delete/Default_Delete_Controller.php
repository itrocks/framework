<?php
namespace SAF\Framework;

class Default_Delete_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$objects = $parameters->getObjects();
		Dao::begin();
		foreach ($objects as $object) {
			if (is_object($object)) {
				Dao::delete($object);
			}
		}
		Dao::commit();
		(new Default_Controller())->run(
			$parameters, $form, $files, $class_name, "deleted"
		);
	}

}
