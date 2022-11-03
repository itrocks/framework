<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Tag;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\View;

/**
 * Custom settings buttons
 */
class Buttons
{

	//------------------------------------------------------------------------------------ getButtons
	/**
	 * @param $object_class object|string
	 * @param $feature_name string|null
	 * @param $caption      string custom element caption (eg 'custom list' or 'custom form')
	 * @param $target       string target component name
	 * @return Button[]
	 */
	public function getButtons(
		string $caption, object|string $object_class, string $feature_name = null,
		string $target = Target::MAIN
	) : array
	{
		$link = $feature_name ? View::link($object_class, $feature_name) : View::link($object_class);
		return [
			Feature::F_SAVE => new Button(
				'Save',
				$link,
				Feature::F_CUSTOM_SAVE,
				[$target, Tag::SUBMIT, Button::HINT => "Save this view as a $caption"]
			),
		];
	}

}
