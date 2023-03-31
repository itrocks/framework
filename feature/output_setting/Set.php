<?php
namespace ITRocks\Framework\Feature\Output_Setting;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Button\Code;
use ITRocks\Framework\Component\Tab;
use ITRocks\Framework\Component\Tab\Tabs_Builder_Class;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Group;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\Has_Properties;
use ITRocks\Framework\Tools\Names;

/**
 * Output settings for personalized forms
 *
 * @override $properties Property[]
 * @property Property[] $properties
 */
class Set extends Setting\Custom\Set
{
	use Has_Properties;

	//--------------------------------------------------------------------------------- AFTER, BEFORE
	const AFTER  = 'after';
	const BEFORE = 'before';

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @var Button[]
	 */
	public array $actions = [];

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * A text php expression where $this is the referent object.
	 * The returned boolean value will tell if the form must be available or not for the object.
	 *
	 * @max_length 60000
	 * @multiline
	 * @var string
	 */
	public string $conditions = '';

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * Additional objects (optional) : store your associated setting objects here
	 *
	 * - Key is the name of the class, value is the stored object
	 * - If the object is a Setting\Object
	 *
	 * @var object[]
	 */
	public array $objects;

	//------------------------------------------------------------------------------------------ $tab
	/**
	 * @var ?Tab
	 */
	public ?Tab $tab = null;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * The title that will be displayed on the top of the output window
	 *
	 * @var string
	 */
	public string $title = '';

	//------------------------------------------------------------------------------------- addAction
	/**
	 * Insert a button before / after another button in the actions bar
	 *
	 * @param $button       Button
	 * @param $where        string @values after, before
	 * @param $where_action string
	 */
	public function addAction(Button $button, string $where = self::AFTER, string $where_action = '')
		: void
	{
		$actions   = [];
		$done      = false;
		$insert_in = -1;
		foreach ($this->actions as $action) {
			if ($action->feature === $where_action) {
				$insert_in = ($where === self::AFTER) ? 2 : 1;
			}
			if (!--$insert_in) {
				$actions[] = $button;
				$done      = true;
			}
			$actions[] = $action;
		}
		if (!$done) {
			$actions[] = $button;
		}
		if ($button->code && $button->code->source) {
			Dao::write($button->code);
		}
		$this->actions = $actions;
	}

	//----------------------------------------------------------------------------------- addProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $add_property_path   string
	 * @param $tab_name            string
	 * @param $where               string after, before,
	 * @param $where_property_path string reference property path for $where
	 * @return Property
	 */
	public function addProperty(
		string $add_property_path, string $tab_name, string $where = self::AFTER,
		string $where_property_path = ''
	) : Property
	{
		$this->initProperties();
		/** @var $add_property Property */
		$add_property = $this->commonAddProperty($add_property_path, $where, $where_property_path);
		$add_property->tab_name = $tab_name;
		return $add_property;
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * This cleanup method is called after loading and getting the current value
	 * in order to avoid crashes when some components of the setting disappeared in the meantime.
	 *
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public function cleanup() : int
	{
		$changes_count = 0;
		foreach (array_keys($this->properties) as $property_path) {
			if (!Reflection_Property::exists($this->getClassName(), $property_path)) {
				unset($this->properties[$property_path]);
				$changes_count ++;
			}
		}
		return $changes_count;
	}

	//--------------------------------------------------------------------- conditionalOutputSettings
	/**
	 * @param $output_settings_list static[]
	 * @param $object               object
	 * @return ?static
	 */
	public static function conditionalOutputSettings(array $output_settings_list, object $object)
		: ?static
	{
		foreach ($output_settings_list as $output_settings) {
			if ($output_settings->conditions) {
				$code         = new Code();
				$code->source = $output_settings->conditions;
				if ($code->execute($object, true)) {
					return $output_settings;
				}
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------- getDefaultTitle
	/**
	 * @return string
	 */
	private function getDefaultTitle() : string
	{
		return Loc::tr(ucfirst(Names::classToDisplay($this->getClassName())));
	}

	//-------------------------------------------------------------------------------- initProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $filter_properties string[] property path
	 * @return Property[]
	 */
	public function initProperties(array $filter_properties = []) : array
	{
		if ($this->commonInitProperties($filter_properties)) {
			return $this->properties;
		}
		$class_name = $this->getClassName();
		/** @noinspection PhpUnhandledExceptionInspection valid class name */
		$class      = new Reflection_Class($class_name);
		$properties = $class->getProperties([T_EXTENDS, T_USE, Reflection_Class::T_SORT]);
		foreach ($properties as $property) {
			if ($property->isPublic() && !$property->isStatic()) {
				/** @noinspection PhpUnhandledExceptionInspection constant */
				$this->properties[$property->name] = Builder::create(
					Property::class, [$class_name, $property->name]
				);
			}
		}
		foreach (Group::of($class) as $group) {
			foreach ($group->values as $property_path) {
				if (str_contains($property_path, DOT)) {
					/** @noinspection PhpUnhandledExceptionInspection constant */
					$this->properties[$property_path] = Builder::create(
						Property::class, [$class_name, $property_path]
					);
				}
			}
		}
		return $this->properties;
	}

	//--------------------------------------------------------------------------------------- initTab
	/**
	 * TODO NORMAL in-tabs management
	 */
	protected function initTab() : void
	{
		if (isset($this->tab)) {
			return;
		}
		$this->tab           = new Tab('main');
		$tabs_builder        = new Tabs_Builder_Class();
		$this->tab->includes = $tabs_builder->build($this->getClass(), array_keys($this->properties));
	}

	//----------------------------------------------------------------------------- propertyHideEmpty
	/**
	 * Sets the property to hide-empty
	 *
	 * @param $property_path string
	 * @param $hide_empty    boolean
	 */
	public function propertyHideEmpty(string $property_path, bool $hide_empty = false) : void
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->hide_empty = $hide_empty;
		}
	}

	//------------------------------------------------------------------------------ propertyReadOnly
	/**
	 * Sets the property to read-only
	 *
	 * @param $property_path string
	 * @param $read_only     boolean
	 */
	public function propertyReadOnly(string $property_path, bool $read_only = false) : void
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->read_only = $read_only;
		}
	}

	//------------------------------------------------------------------------------- propertyTooltip
	/**
	 * Sets the title of the input property
	 *
	 * @param $property_path string
	 * @param $tooltip       string
	 */
	public function propertyTooltip(string $property_path, string $tooltip = '') : void
	{
		$this->initProperties();
		if (isset($this->properties[$property_path])) {
			$this->properties[$property_path]->tooltip = $tooltip;
		}
	}

	//---------------------------------------------------------------------------------- removeAction
	/**
	 * Removes an action using this caption (as this is the only sure unique data)
	 *
	 * @param $caption string
	 * @return boolean true if the action has been removed, false if it was not found
	 */
	public function removeAction(string $caption) : bool
	{
		foreach ($this->actions as $key => $action) {
			if ($action->caption === $caption) {
				unset($this->actions[$key]);
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------------------------- title
	/**
	 * @param $title string
	 * @return string
	 */
	public function title(string $title = '') : string
	{
		if ($title) {
			$this->name  = $title;
			$this->title = $title;
		}
		return $this->name ?: $this->title ?: $this->getDefaultTitle();
	}

	//------------------------------------------------------------------- unconditionalOutputSettings
	/**
	 * @param $output_settings_list static[]
	 * @param $class_name           string
	 * @param $feature              string
	 * @return static
	 */
	public static function unconditionalOutputSettings(
		array $output_settings_list, string $class_name, string $feature
	) : static
	{
		foreach ($output_settings_list as $output_settings) {
			if (!$output_settings->conditions) {
				return $output_settings;
			}
		}
		return static::load($class_name, $feature);
	}

}
