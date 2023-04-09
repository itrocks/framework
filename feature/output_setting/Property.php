<?php
namespace ITRocks\Framework\Feature\Output_Setting;

use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting;
use ReflectionException;

/**
 * Output setting widget property
 */
class Property extends Setting\Property
{

	//----------------------------------------------------------------------------------- $hide_empty
	/**
	 * Hide the property if its value is empty while in display mode
	 * In edit mode and if the property is not read-only, the property will be visible
	 */
	public bool $hide_empty;

	//------------------------------------------------------------------------------------ $read_only
	/** The property will be read-only : the user will not be able to change this value */
	public bool $read_only;

	//------------------------------------------------------------------------------------- $tab_name
	/**
	 * Tab name for grouping
	 * Forces the group where the property will be included into in order to display it in the
	 * matching tab
	 * Empty string means 'out of tabs'
	 * null means 'use the original value of #Group of the matching property / class'
	 */
	public string $tab_name = '';

	//-------------------------------------------------------------------------------------- $tooltip
	/** change the value of title for the property in edit mode */
	public string $tooltip;

	//----------------------------------------------------------------------------------- __construct
	/** @throws ReflectionException */
	public function __construct(string $class_name = null, string $property_path = null)
	{
		parent::__construct($class_name, $property_path);
		$user             = User::of(new Reflection_Property($class_name, $property_path));
		$this->hide_empty = $user->has(User::HIDE_EMPTY);
		$this->read_only  = $user->has(User::READONLY);
		$this->tooltip    = $user->has(User::TOOLTIP);
	}

	//--------------------------------------------------------------------------------- htmlHideEmpty
	/** @noinspection PhpUnused Property_edit.html */
	public function htmlHideEmpty() : string
	{
		return $this->hide_empty ? 'checked' : '';
	}

	//---------------------------------------------------------------------------------- htmlReadOnly
	/** @noinspection PhpUnused Property_edit.html */
	public function htmlReadOnly() : string
	{
		return $this->read_only ? 'checked' : '';
	}

	//----------------------------------------------------------------------------------- htmlTooltip
	/** @noinspection PhpUnused Property_edit.html */
	public function htmlTooltip() : string
	{
		return $this->tooltip;
	}

}
