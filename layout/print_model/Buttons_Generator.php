<?php
namespace ITRocks\Framework\Layout\Print_Model;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\View;

/**
 * Layout model buttons generator
 */
class Buttons_Generator
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	protected $class_name;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	protected $object = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name_object object|string
	 */
	public function __construct($class_name_object = null)
	{
		if (isset($class_name_object)) {
			$this->class_name = Builder::current()->sourceClassName(
				is_string($class_name_object) ? $class_name_object: get_class($class_name_object)
			);
		}
		if (is_object($class_name_object)) {
			$this->object = $class_name_object;
		}
	}

	//------------------------------------------------------------------------------------ getButtons
	/**
	 * @return Button[]
	 */
	public function getButtons()
	{
		$models = Dao::search(['class_name' => $this->class_name], Print_Model::class, Dao::sort());
		foreach ($models as $model) {
			$buttons[] = new Button(
				$model->name,
				View::link($this->object ?: $this->class_name, Feature::F_PRINT, View::link($model)),
				Feature::F_PRINT,
				['.object', Button::OBJECT => $model, View::TARGET => Target::NEW_WINDOW]
			);
		}

		$buttons[] = new Button(
			'New print model',
			View::link(Print_Model::class, Feature::F_ADD, ['class_name' => $this->class_name]),
			Feature::F_ADD
		);

		return $buttons;
	}

}
