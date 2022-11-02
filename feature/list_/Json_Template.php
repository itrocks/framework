<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Tools\Json;
use ITRocks\Framework\Tools\List_Row;
use ITRocks\Framework\View;
use stdClass;

/**
 * Json template for default output feature
 */
class Json_Template extends View\Json\Json_Template
{

	//---------------------------------------------------------------------------------------- render
	/**
	 * Default rendering for a business object to json.
	 *
	 * @return string
	 * @throws Json\Exception
	 */
	public function render() : string
	{
		$json       = new Json();
		$json_array = [];
		$want_full_objects = isset($this->parameters['full']);

		/** @var $element List_Row */
		foreach ($this->parameters[$this->class_name]->elements as $element) {
			// case want full objects
			if ($want_full_objects) {
				$std_object = $json->toStdObject($element->getObject());
			}
			// case want only columns of list settings (default)
			else {
				$std_object     = new stdClass();
				$std_object->id = $element->id();
				foreach ($element->getValues() as $path => $value) {
					$std_object->{$path} = is_object($value) ? (string)$value : $value;
				}
			}
			$json_array[] = $std_object;
		}

		return $json->toJson($json_array);
	}

}
