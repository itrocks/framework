<?php
namespace ITRocks\Framework\Reflection;

use Error;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;
use ITRocks\Framework\Tools\Contextual_Callable;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

/**
 * A reflection property value is a reflection property enriched with its display label and a value
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
	 * @var string
	 */
	public string $display = '';

	//---------------------------------------------------------------------------------- $final_value
	/**
	 * If set to true, $object contains the final value instead of the object containing
	 * the valued property
	 *
	 * @var boolean
	 */
	private bool $final_value;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The object ($final_value = false) or the value ($final_value = true) of the property
	 *
	 * @var mixed
	 */
	private mixed $object;

	//-------------------------------------------------------------------------------------- $tooltip
	/**
	 *  What will be displayed by the tooltip() function
	 *
	 * @var string
	 */
	public string $tooltip = '';

	//----------------------------------------------------------------------------------------- $user
	/**
	 * Set this to true if the property is for an user use (ie for display into a template)
	 *
	 * @var boolean
	 */
	public bool $user;

	//------------------------------------------------------------------------------------ $view_path
	/**
	 * The view path includes any prefix needed by the property building for view, if set
	 * If not set : you should read $path ; and if not : $name
	 *
	 * @var string
	 */
	public string $view_path = '';

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
		object|string $class_name, string $property_name, mixed $object = null,
		bool $final_value = false, bool $user = false
	) {
		parent::__construct($class_name, $property_name);
		$this->final_value = $final_value;
		if (!isset($this->object)) {
			$this->object = (is_object($class_name) && !isset($object) && !$final_value)
				? $class_name
				: $object;
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

	//--------------------------------------------------------------------------------------- display
	/**
	 * Returns the reflection property name display, translated
	 *
	 * @return string
	 */
	public function display() : string
	{
		return $this->display
			?: Loc::tr(Names::propertyToDisplay($this->aliased_path ?: $this->alias));
	}

	//------------------------------------------------------------------------------------ finalValue
	/**
	 * @return boolean
	 */
	public function finalValue() : bool
	{
		return $this->final_value;
	}

	//---------------------------------------------------------------------------------------- format
	/**
	 * @return string
	 */
	public function format() : string
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
	 * @return ?object
	 */
	public function getObject(bool $with_default = false) : ?object
	{
		if ($this->final_value) {
			return null;
		}
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

	//----------------------------------------------------------------------------- getParentProperty
	/**
	 * Gets the parent property for a $property.path
	 *
	 * @noinspection PhpDocMissingThrowsInspection $this->root_class is always valid
	 * @return ?Reflection_Property_Value
	 */
	public function getParentProperty() : ?Reflection_Property_Value
	{
		if (!empty($this->path) && ($i = strrpos($this->path, DOT))) {
			/** @noinspection PhpUnhandledExceptionInspection $this->root_class is always valid */
			return new Reflection_Property_Value(
				$this->object ?: $this->root_class,
				substr($this->path, 0, $i),
				$this->object,
				$this->final_value,
				$this->user
			);
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets value
	 *
	 * @param $object       object|null
	 * @param $with_default boolean if true and property.path, will instantiate objects to get default
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function getValue($object = null, bool $with_default = false) : mixed
	{
		if ($this->user && !$this->final_value) {
			$user_getter = $this->getAnnotation('user_getter');
			if ($user_getter->value) {
				return $this->userGetterValue($user_getter);
			}
		}
		try {
			$value = parent::getValue($object, $with_default);
		}
		catch (Error) {
			$value = null;
		}
		return $value;
	}

	//------------------------------------------------------------------------ getWidgetClassesString
	/**
	 * @return string
	 */
	public function getWidgetClassesString() : string
	{
		$widget_classes = [];
		foreach ($this->getListAnnotations('widget_class') as $annotation) {
			$widget_classes = array_merge($widget_classes, $annotation->values());
		}
		return join(SP, $widget_classes);
	}

	//-------------------------------------------------------------------------------------- isHidden
	/**
	 * @return string 'hidden' if user annotation has 'hidden', else ''
	 */
	public function isHidden() : string
	{
		return $this->getListAnnotation(User_Annotation::ANNOTATION)->has(User_Annotation::HIDDEN)
			? 'hidden'
			: '';
	}

	//---------------------------------------------------------------------------------- isValueEmpty
	/**
	 * Returns true if property value is empty
	 *
	 * @param $value mixed
	 * @return boolean
	 */
	public function isValueEmpty(mixed $value = null) : bool
	{
		return parent::isValueEmpty(func_num_args() ? $value : $this->value());
	}

	//------------------------------------------------------------------------------------- isVisible
	/**
	 * Calculate if the property is visible
	 *
	 * @param $hide_empty_test boolean If false, will be visible even if @user hide_empty is set
	 * @param $hidden_test     boolean If false, will be visible event if @user hidden is set
	 * @param $invisible_test  boolean If false, will be visible event if @user invisible is set
	 * @return boolean
	 */
	public function isVisible(
		bool $hide_empty_test = true, bool $hidden_test = false, bool $invisible_test = true
	) : bool
	{
		$user_annotation = User_Annotation::of($this);
		if (
			!$this->final_value
			&& $user_annotation->has(User_Annotation::ADD_ONLY)
			&& Dao::getObjectIdentifier($this->object)
		) {
			$user_annotation->add(User_Annotation::READONLY);
		}
		return !$this->isStatic()
			&& (!$hidden_test    || !$user_annotation->has(User_Annotation::HIDDEN))
			&& (!$invisible_test || !$user_annotation->has(User_Annotation::INVISIBLE))
			&& (
				($hide_empty_test && (
					!$user_annotation->has(User_Annotation::HIDE_EMPTY)
					|| !$this->isValueEmpty()
				))
				|| (!$hide_empty_test && (
					!$user_annotation->has(User_Annotation::HIDE_EMPTY)
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
	public function pathAsField(bool $class_with_id = false) : string
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

	//------------------------------------------------------------------------------------------ unit
	/**
	 * @return string
	 */
	public function unit() : string
	{
		/** @var $annotation Constant_Or_Method_Annotation */
		$annotation = $this->getAnnotation('unit');
		return $annotation->call($this->getObject() ?: $this->getFinalClassName(), [$this->name]);
	}

	//------------------------------------------------------------------------------- userGetterValue
	/**
	 * Gets @user_getter value
	 *
	 * @param $user_getter Annotation|null
	 * @return mixed
	 */
	public function userGetterValue(Annotation $user_getter = null) : mixed
	{
		if (!isset($user_getter)) {
			$user_getter = $this->getAnnotation('user_getter');
		}
		$object = $this->object;
		if (strpos($this->path, DOT)) {
			foreach (array_slice(explode(DOT, $this->path), 0, -1) as $property_name) {
				$object = $object->$property_name;
			}
		}
		$callable = new Contextual_Callable($user_getter->value, $object);
		$this_user         = $this->user;
		$this->user        = false;
		$user_getter_value = $callable->call($this);
		$this->user        = $this_user;
		return $user_getter_value;
	}

	//----------------------------------------------------------------------------------------- value
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $value        mixed
	 * @param $with_default boolean if true and property.path, will instantiate objects to get default
	 * @return mixed
	 */
	public function value(mixed $value = null, bool $with_default = false) : mixed
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
			$user_getter = $this->getAnnotation('user_getter');
			if ($user_getter->value) {
				return $this->userGetterValue($user_getter);
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection $this is a valid Reflection_Property */
		return $this->final_value ? $this->object : $this->getValue($this->object, $with_default);
	}

}
