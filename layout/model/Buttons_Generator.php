<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 */
	public function __construct($class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = Builder::current()->sourceClassName($class_name);
		}
	}

	//------------------------------------------------------------------------------------ getButtons
	/**
	 * @return Button[]
	 */
	public function getButtons()
	{
		$models = Dao::search(['class_name' => $this->class_name], Model::class, Dao::sort());
		foreach ($models as $model) {
			$buttons[] = new Button(
				$model->name,
				View::link($this->class_name, Feature::F_PRINT, View::link($model)),
				Feature::F_PRINT,
				['.object', Button::OBJECT => $model]
			);
		}

		$buttons[] = new Button(
			'New layout model',
			View::link(Model::class, Feature::F_ADD, ['class_name' => $this->class_name]),
			Feature::F_ADD
		);

		return $buttons;
	}

}
