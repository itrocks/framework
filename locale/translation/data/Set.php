<?php
namespace ITRocks\Framework\Locale\Translation\Data;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Locale\Translation\Data;

/**
 * Data set
 *
 * @display translation data set
 */
class Set
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var Data[]
	 */
	public $elements;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @param $elements      Data[]
	 */
	public function __construct($object = null, $property_name = null, array $elements = [])
	{
		if ($object) {
			$this->class_name = Builder::current()->sourceClassName(get_class($object));
			$this->object     = $object;
		}
		if ($property_name) {
			$this->property_name = $property_name;
		}
		if ($elements || !isset($this->elements))
			$this->elements = $elements;
	}

}
