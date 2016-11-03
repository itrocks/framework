<?php
namespace ITRocks\Framework\Widget\Output_Setting;

use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Can_Be_Empty;

/**
 * Output setting widget property
 */
class Property implements Can_Be_Empty
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * Display must be stored already translated
	 *
	 * @var string
	 */
	public $display;

	//----------------------------------------------------------------------------------- $hide_empty
	/**
	 * Hide the property if its value if empty while in display mode
	 * In edit mode and if the property is not read-only, the property will be visible
	 *
	 * @var boolean
	 */
	public $hide_empty;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public $path;

	//------------------------------------------------------------------------------------ $read_only
	/**
	 * The property will be read-only : the user will not be able to change this value
	 *
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
			$property         = new Reflection_Property_Value($class_name, $property_path);
			$this->display    = $property->display();
			$this->path       = $property->path;
			$user_annotation  = $property->getListAnnotation(User_Annotation::ANNOTATION);
			$this->hide_empty = $user_annotation->has(User_Annotation::HIDE_EMPTY);
			$this->read_only  = $user_annotation->has(User_Annotation::READONLY);
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

	//--------------------------------------------------------------------------------- htmlHideEmpty
	/**
	 * @return string
	 */
	public function htmlHideEmpty()
	{
		return $this->hide_empty ? 'checked' : '';
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
