<?php
namespace ITRocks\Framework\Feature\Output;

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
	public function render() : string
	{
		$json = new Json();
		return $json->toJson($json->toStdObject($this->parameters[$this->class_name]));
	}

}
