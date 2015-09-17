<?php
namespace SAF\Framework\Widget\Output_Setting;

use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Reflection_Property_Value;

/**
 * Output setting widget property
 */
class Property
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * @var string
	 */
	public $display;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public $path;

	//------------------------------------------------------------------------------------ $read_only
	/**
	 * @var boolean
	 */
	public $read_only;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $property_path string
	 */
	public function __construct($class_name = null, $property_path = null)
	{
		if (isset($class_name) && isset($property_path)) {
			$property        = new Reflection_Property_Value($class_name, $property_path);
			$this->display   = Loc::tr($property->display());
			$this->path      = $property->path;
			$this->read_only = $property->getAnnotation('read_only')->value;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->display);
	}

	//---------------------------------------------------------------------------------- htmlReadOnly
	/**
	 * @return string
	 */
	public function htmlReadOnly()
	{
		return $this->read_only ? 'checked' : '';
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean
	 */
	public function isEmpty()
	{
		return !(strval($this->display) || strval($this->path));
	}

}
