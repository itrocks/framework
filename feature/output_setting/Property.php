<?php
namespace ITRocks\Framework\Feature\Output_Setting;

use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Setting;
use ReflectionException;

/**
 * Output setting widget property
 */
class Property extends Setting\Property
{

	//----------------------------------------------------------------------------------- $hide_empty
	/**
	 * Hide the property if its value if empty while in display mode
	 * In edit mode and if the property is not read-only, the property will be visible
	 *
	 * @var boolean
	 */
	public bool $hide_empty;

	//------------------------------------------------------------------------------------ $read_only
	/**
	 * The property will be read-only : the user will not be able to change this value
	 *
	 * @var boolean
	 */
	public bool $read_only;

	//------------------------------------------------------------------------------------- $tab_name
	/**
	 * Tab name for grouping
	 * Forces the group where the property will be included into in order to display it in the
	 * matching tab
	 * Empty string ('') means 'out of tabs'
	 * null means 'use the original value of @group of the matching property / class'
	 *
	 * @var string
	 */
	public string $tab_name = '';

	//-------------------------------------------------------------------------------------- $tooltip
	/**
	 * change the value of title for the property in edit mode
	 *
	 * @var string
	 */
	public string $tooltip;

	//----------------------------------------------------------------------------------- __construct

	/**
	 * @param $class_name    string|null
	 * @param $property_path string|null
	 * @throws ReflectionException
	 */
	public function __construct(string $class_name = null, string $property_path = null)
	{
		parent::__construct($class_name, $property_path);
		if (!isset($this->property)) {
			return;
		}
		$user_annotation  = $this->property->getListAnnotation(User_Annotation::ANNOTATION);
		$this->hide_empty = $user_annotation->has(User_Annotation::HIDE_EMPTY);
		$this->read_only  = $user_annotation->has(User_Annotation::READONLY);
		$this->tooltip    = $user_annotation->has(Tooltip_Annotation::ANNOTATION);
	}

	//--------------------------------------------------------------------------------- htmlHideEmpty
	/**
	 * @noinspection PhpUnused Property_edit.html
	 * @return string
	 */
	public function htmlHideEmpty() : string
	{
		return $this->hide_empty ? 'checked' : '';
	}

	//---------------------------------------------------------------------------------- htmlReadOnly
	/**
	 * @noinspection PhpUnused Property_edit.html
	 * @return string
	 */
	public function htmlReadOnly() : string
	{
		return $this->read_only ? 'checked' : '';
	}

	//----------------------------------------------------------------------------------- htmlTooltip
	/**
	 * @noinspection PhpUnused Property_edit.html
	 * @return string
	 */
	public function htmlTooltip() : string
	{
		return $this->tooltip;
	}

}
