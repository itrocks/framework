<?php
namespace ITRocks\Framework\Feature\Output_Setting;

use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
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

	//------------------------------------------------------------------------------------- $tab_name
	/**
	 * Tab name for grouping
	 * Forces the group where the property will be included into in order to display it in the
	 * matching tab
	 * Empty string ('') means 'out of tabs'
	 * null means 'use the original value of @group of the matching property / class'
	 *
	 * @var string|null
	 */
	public $tab_name;

	//-------------------------------------------------------------------------------------- $tooltip
	/**
	 * change the value of title for the property in edit mode
	 *
	 * @var string
	 */
	public $tooltip;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    string
	 * @param $property_path string
	 */
	public function __construct($class_name = null, $property_path = null)
	{
		if (isset($class_name) && isset($property_path)) {
			/** @noinspection PhpUnhandledExceptionInspection class name and property must be valid */
			$property         = new Reflection_Property_Value($class_name, $property_path);
			$this->display    = $property->display();
			$this->path       = $property->path;
			$user_annotation  = $property->getListAnnotation(User_Annotation::ANNOTATION);
			$this->hide_empty = $user_annotation->has(User_Annotation::HIDE_EMPTY);
			$this->read_only  = $user_annotation->has(User_Annotation::READONLY);
			$this->tooltip    = $user_annotation->has(Tooltip_Annotation::ANNOTATION);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
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

	//----------------------------------------------------------------------------------- htmlTooltip
	/**
	 * @return string
	 */
	public function htmlTooltip()
	{
		return $this->tooltip;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean
	 */
	public function isEmpty() : bool
	{
		return !(strval($this->display) || strval($this->path));
	}

}
