<?php
namespace ITRocks\Framework\Feature\List_Save;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\List_\Item;

/**
 * List-save controller
 */
class Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$class_name = Builder::className(get_class($parameters->getMainObject()));
		$write      = [];
		foreach ($form as $id_property => $value) {
			[$id, $property_name] = explode('_', $id_property, 2);
			$write[$id][$property_name] = $value;
		}
		Dao::begin();
		foreach ($write as $id => $write_properties) {
			$object = Dao::read($id, $class_name);
			foreach ($write_properties as $property_name => $value) {
				$object->$property_name = $value;
			}
			Dao::write($object, Dao::only(array_keys($write_properties)));
		}
		Dao::commit();
		Main::$current->redirect(
			View::link(Names::classToSet($class_name), Feature::F_LIST),
			Target::MAIN
		);
		return new Item(Loc::tr('Saved') . DOT);
	}

}
