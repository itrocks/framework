<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\View;

/**
 */
abstract class Message
{

	//--------------------------------------------------------------------------------------- display
	/**
	 * A shortcut to display messages and error messages into the #messages / #query zones
	 *
	 * @param $object   object
	 * @param $messages array|string
	 * @param $errors   array|string
	 * @return string
	 */
	public static function display(object $object, array|string $messages, array|string $errors = [])
		: string
	{
		$class_name = Builder::current()->sourceClassName(get_class($object));
		$parameters = [
			$class_name          => $object,
			'messages'           => is_array($messages) ? $messages : [$messages],
			'errors'             => is_array($errors)   ? $errors   : [$errors],
			Parameter::AS_WIDGET => true
		];
		return View::run($parameters, [], [], $class_name, Feature::F_MESSAGE);

	}

}
