<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Tools\Contextual_Callable;
use ITRocks\Framework\Tools\Names;

/**
 * A reflection property value is a reflection property enriched with it's display label and a value
 */
class Reflection_Property_Value extends Reflection_Property
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * What will be displayed by the display() function
	 *
	 * Keep this null to calculate automatically, fill this only to force display
	 * The display stored here must already be translated
	 *
	 * @var string|null
	 */
	public $display = null;

	//---------------------------------------------------------------------------------- $final_value
	/**
	 * If set to true, $object contains the final value instead of the object containing
	 * the valued property
	 *
	 * @var boolean
	 */
	private $final_value;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The object ($final_value = false) or the value ($final_value = true) of the property
	 *
	 * @var object
	 */
	private $object;

	//-------------------------------------------------------------------------------------- $tooltip
	/**
	 *  What will be displayed by the tooltip() function
	 *
	 * @var string|null
	 */
	public $tooltip = null;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * Set this to true if the property is for an user use (ie for display into a template)
	 *
	 * @var boolean
	 */
	private $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a reflection property with value
	 *
	 * @example
	 * $pv = new Reflection_Property_Value('Class_Name', 'property_name', $object);
	 * @param $class_name    string
	 * @param $property_name string
	 * @param $object        object|mixed the object containing the value, or the value itself
	 *        (in this case set $final_value tu true)
	 * @param $final_value   boolean set to true if $object is a final value instead of the object
	 *        containing the valued property
	 * @param $user          boolean set to true if the property value will be used into an user
	 *        display (ie an HTML template)
	 */
	public function __construct(
		$class_name, $property_name, $object = null, $final_value = false, $user = false
	) {
		parent::__construct($class_name, $property_name);
		$this->final_value = $final_value;
		if (!isset($this->object)) {
			$this->object = $object;
		}
		else {
			trigger_error(
				'DEAD CODE ? object is set for ' . $class_name . '::' . $property_name,
				E_USER_WARNING
			);
		}
		$this->user = $user;
	}

	//----------------------------------------------------------------------------------------- __get
	/**
	 * Sets additional properties to matching Reflection_Property
	 * (common for all instances of this property)
	 *
	 * @param $key string
	 * @return mixed
	 */
	public function __get($key)
	{
		$property = new Reflection_Property($this->class, $this->name);
		$value = isset($property->$key) ? $property->$key : null;
		trigger_error(
			'Reflection_Property_Value::__get(' . $key . ') = ' . $value . ' MAY CRASH !',
			E_USER_WARNING
		);
		return $value;
	}

	//----------------------------------------------------------------------------------------- __set
	/**
	 * Sets additional properties to matching Reflection_Property
	 * (common for all instances of this property)
	 *
	 * @param $key   string
	 * @param $value mixed
	 */
	public function __set($key, $value)
	{
		trigger_error(
			'Reflection_Property_Value::__set(' . $key . ') = ' . $value . ' MAY CRASH !',
			E_USER_WARNING
		);
		$property = (new Reflection_Property($this->class, $this->name));
		$property->$key = $value;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * Returns the reflection property name display, translated
	 *
	 * @return string
	 */
	public function display()
	{
		return $this->display
			? $this->display
			: Loc::tr(Names::propertyToDisplay($this->aliased_path ? $this->aliased_path : $this->alias));
	}

	//---------------------------------------------------------------------------------------- format
	/**
	 * @return mixed
	 */
	public function format()
	{
		return (new Reflection_Property_View($this))->getFormattedValue(
			$this->object, $this->final_value
		);
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets the object containing the value (null if the value was set as a value)
	 *
	 * @return object|null
	 */
	public function getObject()
	{
		$object = $this->object;
		if (strpos($this->path, DOT)) {
			foreach (array_slice(explode(DOT, $this->path), 0, -1) as $property_name) {
				if (!$object) {
					break;
				}
				if (!empty($property_name)){
					$object = $object->$property_name;
				}
			}
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------- isHidden
	/**
	 * @return string|boolean 'hidden' if user annotation has 'hidden', else false
	 */
	public function isHidden()
	{
		return $this->getListAnnotation(User_Annotation::ANNOTATION)->has(User_Annotation::HIDDEN)
			? 'hidden'
			: false;
	}

	//---------------------------------------------------------------------------------- isValueEmpty
	/**
	 * Returns true if property value is empty
	 *
	 * @param $value mixed
	 * @return boolean
	 */
	public function isValueEmpty($value = null)
	{
		return parent::isValueEmpty(func_num_args() ? $value : $this->value());
	}

	//------------------------------------------------------------------------------------- isVisible
	/**
	 * Calculate if the property is visible
	 *
	 * @param $hide_empty_test boolean If false, will be shown even if HIDE_EMPTY is set and value
	 *        is empty
	 * @return boolean
	 */
	public function isVisible($hide_empty_test = true)
	{
		$user_annotation = $this->getListAnnotation(User_Annotation::ANNOTATION);
		return !$this->isStatic()
			&& !$user_annotation->has(User_Annotation::INVISIBLE)
			&& (
				($hide_empty_test && (
					!$user_annotation->has(User_Annotation::HIDE_EMPTY)
					|| !$this->isValueEmpty()
				))
				|| (!$hide_empty_test && (
					!$user_annotation->has(User_Annotation::READONLY)
					|| !$user_annotation->has(User_Annotation::HIDE_EMPTY)
					|| !$this->isValueEmpty()
				))
			);
	}

	//--------------------------------------------------------------------------------------- tooltip
	/**
	 * Returns the reflection property tooltip
	 *
	 * @return string
	 */
	public function tooltip()
	{
		return $this->tooltip;
	}

	//----------------------------------------------------------------------------------------- value
	/**
	 * @param $value mixed
	 * @return mixed
	 */
	public function value($value = null)
	{
		if ($value !== null) {
			if ($this->final_value) {
				$this->object = $value;
			}
			else {
				$this->setValue($this->object, $value);
			}
		}
		if ($this->user && !$this->final_value) {
			$user_getter = $this->getAnnotation('user_getter')->value;
			if ($user_getter) {
				$callable = new Contextual_Callable($user_getter, $this->object);
				return $callable->call();
			}
		}
		return $this->final_value ? $this->object : $this->getValue($this->object);
	}

}
