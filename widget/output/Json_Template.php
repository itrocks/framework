<?php
namespace ITRocks\Framework\Widget\Output;

use ITRocks\Framework\Tools\Json;
use ITRocks\Framework\View;

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
	public function render()
	{
		$json   = new Json();
		$result = $json->toJson($json->toStdObject($this->parameters[$this->class_name]));

		return $result;
	}

}
