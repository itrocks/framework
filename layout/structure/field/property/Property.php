<?php
namespace ITRocks\Framework\Layout\Structure\Field;

use ITRocks\Framework\Layout\Structure\Field;

/**
 * Layout structure property field
 */
class Property extends Field
{

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The path of the property, starting from the layout model context class
	 *
	 * @var string
	 */
	public string $property_path;

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @return string
	 */
	public function dump(int $level = 0) : string
	{
		return parent::dump($level) . ' = ' . $this->property_path;
	}

}
