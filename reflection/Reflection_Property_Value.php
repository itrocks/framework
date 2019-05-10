<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;
use ITRocks\Framework\Tools\Contextual_Callable;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

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

	//------------------------------------------------------------------------------------ $view_path
	/**
	 * The view path includes any prefix needed by the property building for view, if set
	 * If not set : you should read $path ; and if not : $name
	 *
	 * @var string
	 */
	public $view_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a reflection property with value
	 *
	 * @example
	 * $pv = new Reflection_Property_Value('Class_Name', 'property_name', $object);
	 * @param $class_name    object|string
	 * @param $property_name string
	 * @param $object        object|mixed the object containing the value, or the value itself
	 *        (in this case set $final_value tu true)
	 * @param $final_value   boolean set to true if $object is a final value instead of the object
	 *        containing the valued property
	 * @param $user          boolean set to true if the property value will be used into an user
	 *        display (ie an HTML template)
	 * @throws ReflectionException
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
			if (is_object($class_name)) {
				$class_name = get_class($class_name);
			}
			trigger_error(
				'DEAD CODE ? object is set for ' . $class_name . '::' . $property_name, E_USER_WARNING
			);
		}
		$this->user = $user;
	}

	//----------------------------------------------------------------------------------------- __get
	/**
	 * Sets additional properties to matching Reflection_Property
	 * (common for all instances of this property)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $key string
	 * @return mixed
	 */
	public function __get($key)
	{
		/** @noinspection PhpUnhandledExceptionInspection $this is a valid Reflection_Property */
		$property = new Reflection_Property($this->class, $this->name);
		$value    = isset($property->$key) ? $property->$key : null;
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $key   string
	 * @param $value mixed
	 */
	public function __set($key, $value)
	{
		trigger_error(
			'Reflection_Property_Value::__set(' . $key . ') = ' . $value . ' MAY CRASH !',
			E_USER_WARNING
		);
		/** @noinspection PhpUnhandledExceptionInspection $this is a valid Reflection_Property */
		$property = new Reflection_Property($this->class, $this->name);
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
			?: Loc::tr(
				Names::propertyToDisplay($this->aliased_path ? $this->aliased_path : $this->alias)
			);
	}

	//------------------------------------------------------------------------------------ finalValue
	/**
	 * @return boolean
	 */
	public function finalValue()
	{
		return $this->final_value;
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $with_default boolean false
	 * @return object|null
	 */
	public function getObject($with_default = false)
	{
		$object = $this->object;
		if (strpos($this->path, DOT)) {
			foreach (array_slice(explode(DOT, $this->path), 0, -1) as $property_name) {
				if (!$object) {
					if ($with_default && isset($previous_object) && isset($previous_property_name)) {
						/** @noinspection PhpUnhandledExceptionInspection the property path must be valid */
						$previous_property = new Reflection_Property(
							get_class($previous_object), $previous_property_name
						);
						/** @noinspection PhpUnhandledExceptionInspection the property type must be valid */
						$object = Builder::create($previous_property->getType()->getElementTypeAsString());
					}
					else {
						break;
					}
				}
				if (!empty($property_name)) {
					$previous_object        = $object;
					$previous_property_name = $property_name;
					$object                 = $object->$property_name;
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
	 * @param $hide_empty_test boolean If false, will be visible even if @user hide_empty is set
	 * @param $hidden_test boolean If false, will be visible event if @user hidden is set
	 * @param $invisible_test boolean If false, will be visible event if @user invisible is set
	 * @return boolean
	 */
	public function isVisible($hide_empty_test = true, $hidden_test = false, $invisible_test = true)
	{
		$user_annotation = $this->getListAnnotation(User_Annotation::ANNOTATION);
		return !$this->isStatic()
			&& (!$hidden_test    || !$user_annotation->has(User_Annotation::HIDDEN))
			&& (!$invisible_test || !$user_annotation->has(User_Annotation::INVISIBLE))
			&& (
				($hide_empty_test && (
					!$user_annotation->has(User_Annotation::HIDE_EMPTY)
					|| !$this->isValueEmpty()
				))
				|| (!$hide_empty_test && (
					!$user_annotation->has(User_Annotation::CREATE_ONLY)
					|| !$user_annotation->has(User_Annotation::HIDE_EMPTY)
					|| !$user_annotation->has(User_Annotation::READONLY)
					|| !$this->isValueEmpty()
				))
			);
	}

	//----------------------------------------------------------------------------------- pathAsField
	/**
	 * Returns path formatted as field : uses [] instead of .
	 *
	 * @example if $this->path is 'a.field.path', will return 'a[field][path]'
	 * @param $class_with_id boolean if true, will append [id] or prepend id_ for class fields
	 * @return string
	 */
	public function pathAsField($class_with_id = false)
	{
		$path = Names::propertyPathToField($this->view_path ?: $this->path);
		if ($class_with_id && $this->getType()->isClass()) {
			if (strpos($path, DOT)) {
				$path .= '[id]';
			}
			else {
				$path = 'id_' . $path;
			}
		}
		return $path;
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

	//------------------------------------------------------------------------------------------ unit
	/**
	 * @return string
	 */
	public function unit()
	{
		/** @var $annotation Constant_Or_Method_Annotation */
		$annotation = $this->getAnnotation('unit');
		return $annotation->call($this->getObject() ?: $this->getFinalClassName(), [$this->name]);
	}

	//----------------------------------------------------------------------------------------- value
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $value        mixed
	 * @param $with_default boolean if true and property.path, will instantiate objects to get default
	 * @return mixed
	 */
	public function value($value = null, $with_default = false)
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
				$object = $this->object;
				if (strpos($this->path, DOT)) {
					foreach (array_slice(explode(DOT, $this->path), 0, -1) as $property_name) {
						$object = $object->$property_name;
					}
				}
				$callable = new Contextual_Callable($user_getter, $object);
				return $callable->call();
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection $this is a valid Reflection_Property */
		return $this->final_value ? $this->object : $this->getValue($this->object, $with_default);
	}

}
