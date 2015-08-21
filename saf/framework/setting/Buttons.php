<?php
namespace SAF\Framework\Setting;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Target;
use SAF\Framework\Tools\Names;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;

/**
 * Custom settings buttons
 */
class Buttons
{

	//------------------------------------------------------------------------------------ getButtons
	/**
	 * @param $class_name string
	 * @param $caption    string custom element caption (eg 'custom list' or 'custom form')
	 * @return Button[]
	 */
	public function getButtons($class_name, $caption)
	{
		$class_names = Names::classToSet($class_name);
		return [
			Feature::F_WRITE => new Button(
				'Save',
				View::link($class_names),
				'custom_save',
				[Target::MAIN, '.submit', 'title' => "Save this view as a $caption"]
			),
		];
	}

}
