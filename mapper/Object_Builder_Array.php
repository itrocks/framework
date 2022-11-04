<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Combo\Fast_Add;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Encrypt_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Password_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\User_Error_Exception;

/**
 * Build an object and it's property values from data stored into a recursive array
 *
 * TODO LOW Do we need to do if (!isset($object->$property_name)) into all builders ? Please check.
 */
class Object_Builder_Array
{

	//------------------------------------------------------------------------------------- $builders
	/**
	 * @var Object_Builder_Array[] key is the property name
	 */
	private array $builders;

	//-------------------------------------------------------------------------------- $built_objects
	/**
	 * The objects that where built : get it with getBuiltObjects()
	 *
	 * @var Built_Object[]
	 */
	private array $built_objects = [];

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	private Reflection_Class $class;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * Store composite object to attach the @composite property of a Component built object
	 *
	 * @var object|null
	 */
	public ?object $composite = null;

	//------------------------------------------------------------------------------------ $from_form
	/**
	 * True (default) if apply build specifics for arrays that come from an input form :
	 * - apply arrayFormRevert to split key positions
	 * - apply widgets
	 * Setting this to false disable these specific processes.
	 *
	 * @var boolean
	 */
	private bool $from_form;

	//-------------------------------------------------------------------- $ignore_unknown_properties
	/**
	 * If false, build() will generate an error if the array contains data for properties that do not
	 * exist in object's class.
	 * With true, you do not generate this error but we ignore unknown properties
	 * With null, we store unknown properties into the object
	 *
	 * @var boolean|null
	 */
	public ?bool $ignore_unknown_properties = false;

	//-------------------------------------------------------------------- $null_if_empty_sub_objects
	/**
	 * @var boolean set sub-objects null if empty, even if main object accepts null if empty
	 */
	public bool $null_if_empty_sub_objects = false;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * Properties list, set by start()
	 *
	 * @var Reflection_Property[]
	 */
	private array $properties;

	//-------------------------------------------------------------------------------------- $started
	/**
	 * True when start() is called. Back to false by stop(). This avoids resetting data when recurse
	 *
	 * @var boolean
	 */
	private bool $started = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 * @param $from_form  boolean Set this to false to disable interpretation of arrays coming from
	 *                    forms : arrayFormRevert, widgets. You should always set this to false if
	 *                    your array does not come from an input form.
	 * @param $composite  object|null Reference to the composite object if we build a Component
	 */
	public function __construct(
		string $class_name = null, bool $from_form = true, object $composite = null
	) {
		$this->from_form = $from_form;
		$this->composite = $composite;
		if (isset($class_name)) {
			$this->setClass($class_name);
		}
	}

	//------------------------------------------------------------------------------------ __destruct
	public function __destruct()
	{
		if ($this->started) {
			$this->stop();
		}
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $array                array
	 * @param $object               object|null
	 * @param $null_if_empty        boolean
	 * @param $ignore_property_name string|null
	 * @return ?object
	 * @throws User_Error_Exception
	 */
	public function build(
		array $array, object $object = null, bool $null_if_empty = false,
		string $ignore_property_name = null
	) : ?object
	{
		// first "null-if-empty" pass : on raw data
		if ($null_if_empty) {
			$ok = false;
			foreach ($array as $value) {
				if ($value) {
					$ok = true;
					break;
				}
			}
			if (!$ok) {
				return null;
			}
		}
		// start, build
		if (!$this->started) {
			$this->start(isset($object) ? get_class($object) : null);
		}
		$search = $this->initObject($array, $object);
		$build  = new Object_Builder_Array_Tool(
			$array, $object, $null_if_empty, $ignore_property_name, $search
		);
		$this->buildProperties($build);
		$this->buildSubObjects($build);
		// second "null-if-empty" pass : on calculated object
		if ($build->is_null && count($array)) {
			return null;
		}
		// complete the generated object with the object read from the data store
		if ($build->read_properties) {
			$object = $this->readObject($object, $build->read_properties);
		}
		// after build
		$this->built_objects[] = new Built_Object($build->object);
		foreach ($this->class->getAnnotations('after_build_array') as $after) {
			call_user_func_array([$object, $after->value], [&$array]);
		}
		return $object;
	}

	//------------------------------------------------------------------------------- buildBasicValue
	/**
	 * @param $property Reflection_Property
	 * @param $value    array|boolean|float|integer|null|string
	 * @return array|boolean|float|integer|string|null
	 * @throws User_Error_Exception
	 */
	private function buildBasicValue(
		Reflection_Property $property, array|bool|float|int|null|string $value
	) : array|bool|float|int|string|null
	{
		if (!is_null($value) || !Null_Annotation::of($property)->value) {
			if (is_string($value)) {
				$value = trim($value);
			}
			$value = match($property->getType()->asString()) {
				Type::BOOLEAN => !(empty($value) || in_array($value, [_FALSE, .0, 0, '0'], true)),
				Type::INTEGER => Loc::integerToIso($value),
				Type::FLOAT   => Loc::floatToIso($value),
				default       => $value
			};
		}
		/** @noinspection PhpExpressionAlwaysNullInspection inspector bug */
		return $value;
	}

	//------------------------------------------------------------------------------- buildCollection
	/**
	 * Accepted arrays :
	 * $array[$object_number][$property_name] = $value
	 * $array[$property_name][$object_number] = $value
	 * $array[0][$column_number] = 'property_name' then $array[$object_number][$column_number] = $value
	 *
	 * @param $class_name     string
	 * @param $old_collection object[] the value of the collection, read from the object
	 * @param $array          array
	 * @param $null_if_empty  boolean
	 * @param $composite      object|null the composite object, if linked
	 * @return object[]
	 * @throws User_Error_Exception
	 */
	public function buildCollection(
		string $class_name, array $old_collection, array $array, bool $null_if_empty = false,
		object $composite = null
	) : array
	{
		$collection = [];
		if ($array) {
			$builder = new Object_Builder_Array($class_name, $this->from_form, $composite);
			if ($this->null_if_empty_sub_objects) {
				$builder->null_if_empty_sub_objects = true;
			}
			// replace $array[$property_name][$object_number] with $array[$object_number][$property_name]
			reset($array);
			if ($this->from_form && !is_numeric(key($array))) {
				$array = arrayFormRevert($array, false);
			}
			// check if the first row contains column names
			$first_row = reset($array);
			reset($first_row);
			if ($combine = is_numeric(key($first_row))) {
				unset($array[key($array)]);
			}
			$old_collection_keys = [];
			foreach ($old_collection as $key => $old_element) if (isset($old_element->id)) {
				$old_collection_keys[$old_element->id] = $key;
			}
			foreach ($array as $key => $element) {
				if ($combine) {
					$element = array_combine($first_row, $element);
				}
				$object = (isset($element['id']) && isset($old_collection_keys[$element['id']]))
					? $old_collection[$old_collection_keys[$element['id']]]
					: null;
				$object = $builder->build(
					$element, $object, $this->null_if_empty_sub_objects || $null_if_empty
				);
				if (isset($object)) {
					$collection[$key] = $object;
				}
			}
		}
		return $collection;
	}

	//--------------------------------------------------------------------------- buildDottedProperty
	/**
	 * @param $build         Object_Builder_Array_Tool
	 * @param $property_name string The name of the property
	 * @param $value         mixed The value of the property
	 * @param $pos           integer The position of the DOT into the $property_name
	 */
	private function buildDottedProperty(
		Object_Builder_Array_Tool $build, string $property_name, mixed $value, int $pos
	) {
		$property_path = substr($property_name, $pos + 1);
		$property_name = substr($property_name, 0, $pos);
		$this->extractAsterisk($property_name);
		$property = $this->properties[$property_name] ?? null;
		if (isset($property)) {
			$build->objects[$property->name][$property_path] = $value;
		}
	}

	//------------------------------------------------------------------------------- buildIdProperty
	/**
	 * If an id_foo property is set and not empty, it can be set and associated object is removed
	 * id_foo must always be set before any forced foo[sub_property] values into the array
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object        object
	 * @param $property_name string must start with 'id_'
	 * @param $value         string
	 * @param $null_if_empty boolean
	 * @return boolean
	 */
	private function buildIdProperty(
		object $object, string $property_name, string $value, bool $null_if_empty
	) : bool
	{
		$is_null            = $null_if_empty;
		$real_property_name = substr($property_name, 3);
		$property           = $this->properties[$real_property_name];
		if (empty($value)) {
			$value = $property->getAnnotation('null')->value ? null : 0;
		}
		if (isset($object->$real_property_name) && (
			empty($value)
			|| !isset($object->$real_property_name->id)
			|| ($value != $object->$real_property_name->id)
		)) {
			unset($object->$real_property_name);
		}
		if ($property->getType()->isAbstractClass() && str_contains($value, ':')) {
			$class_property_name = $property_name . '_class';
			[$object->$class_property_name, $value] = explode(':', $value);
		}
		// forces the call to the AOP / setter, if there is one for the property
		if ($value && (!isset($object->$property_name) || ($value != $object->$property_name))) {
			/** @noinspection PhpUnhandledExceptionInspection object */
			$property = new Reflection_Property($object, $real_property_name);
			if (method_exists($object, '_' . $real_property_name . '_write')) {
				$dao                         = Dao::get($property->getAnnotation('dao')->value);
				$object->$real_property_name = $dao->read($value, $property->getType()->asString());
			}
		}
		if (!isset($object->$property_name) || ($value != $object->$property_name)) {
			$object->$property_name = $value;
		}
		if (
			!$property->isValueEmptyOrDefault($value)
			&& $property->getAnnotation('empty_check')->value
		) {
			$is_null = false;
		}
		return $is_null;
	}

	//-------------------------------------------------------------------------------------- buildMap
	/**
	 * @param $array      array
	 * @param $class_name string the name of the class to build each element
	 * @return integer[]
	 * @throws User_Error_Exception
	 */
	public function buildMap(array $array, string $class_name) : array
	{
		$map = [];
		// file identifiers are copied to array values
		if (isset($array['id'])) {
			foreach ($array['id'] as $key => $identifier) {
				if (!$array[$key]) {
					$array[$key] = $identifier;
				}
			}
			unset($array['id']);
		}
		// build each element
		foreach ($array as $key => $element) {
			if (empty($element)) {
				continue;
			}
			if (is_array($element)) {
				$built = (new Object_Builder_Array($class_name, $this->from_form))->build(
					$element, null, true
				);
				if ($built) {
					$map[$key] = $built;
				}
			}
			else {
				if (
					is_string($element)
					&& !is_numeric($element)
					&& str_contains($element, ':')
					&& is_numeric(rParse($element, ':'))
					&& class_exists(lParse($element, ':'))
				) {
					[$real_class_name, $element] = explode(':', $element);
					if (!isA($real_class_name, $class_name)) {
						// this is for security purpose, to disallow unauthorized classes injection
						trigger_error(
							$real_class_name . ' must inherit abstract/trait ' . $class_name,
							E_USER_ERROR
						);
					}
					$class_name = $real_class_name;
				}
				if (
					is_string($element)
					&& !is_numeric($element)
					&& is_a($class_name, Fast_Add::class, true)
				) {
					/** @see Fast_Add::fromString */
					$map[$key] = call_user_func([$class_name, 'fromString'], $element);
				}
				else {
					$map[$key] = is_object($element) ? $element : Dao::read($element, $class_name);
				}
			}
		}
		return $map;
	}

	//------------------------------------------------------------------------------ buildObjectValue
	/**
	 * @param $class_name    string the class name of the object to build
	 * @param $object        ?object the value of the object before build (may be null if no object)
	 * @param $array         array  the values of the properties to be replaced into the object
	 * @param $null_if_empty boolean
	 * @param $composite     ?object The composite object (set it only if property is a @component)
	 * @return ?object
	 * @throws User_Error_Exception
	 */
	private function buildObjectValue(
		string $class_name, ?object $object, array $array, bool $null_if_empty, ?object $composite
	) : ?object
	{
		$builder = new Object_Builder_Array($class_name, $this->from_form, $composite);
		$object  = $builder->build($array, $object, $this->null_if_empty_sub_objects || $null_if_empty)
			?: $object;
		if ($object && $composite && isA($class_name, Component::class)) {
			array_pop($builder->built_objects);
		}
		$this->built_objects = array_merge($this->built_objects, $builder->built_objects);
		return $object;
	}

	//------------------------------------------------------------------------------- buildProperties
	/**
	 * @param $build Object_Builder_Array_Tool
	 * @throws User_Error_Exception
	 */
	private function buildProperties(Object_Builder_Array_Tool $build)
	{
		foreach ($build->array as $property_name => $value) {
			if ($pos = strpos($property_name, DOT)) {
				$this->buildDottedProperty($build, $property_name, $value, $pos);
			}
			else {
				$this->buildSimpleProperty($build, $property_name, $value);
			}
		}
	}

	//--------------------------------------------------------------------------------- buildProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $build    Object_Builder_Array_Tool
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return boolean true if property value is null
	 * @throws User_Error_Exception
	 */
	private function buildProperty(
		Object_Builder_Array_Tool $build, Reflection_Property $property, mixed $value
	) : bool
	{
		$null_if_empty = $build->null_if_empty;
		$object        = $build->object;
		$is_null       = $null_if_empty;
		$property_name = $property->name;
		$type          = $property->getType();
		// use widget
		if (
			$this->from_form
			&& ($builder = Widget_Annotation::of($property)->value)
			&& is_a($builder, Property::class, true)
		) {
			/** @noinspection PhpUnhandledExceptionInspection widget builder class name must be valid */
			/** @var $builder Property */
			$builder             = Builder::create($builder, [$property, $value]);
			$value2              = $builder->buildValue($object, $null_if_empty);
			$this->built_objects = array_merge($this->built_objects, $builder->built_objects);
			if ($value2 !== Property::DONT_BUILD_VALUE) {
				$value = $value2;
				$done  = true;
			}
		}
		if (!isset($done)) {
			if ($type->isBasic(false)) {
				// password
				if ($encryption = (
					Encrypt_Annotation::of($property)->value ?: Password_Annotation::of($property)->value
				)) {
					if ($value === Password::UNCHANGED) {
						return true;
					}
					$value = (new Password($value, $encryption))->encrypted();
					if ($value === Password::UNCHANGED) {
						return true;
					}
				}
				// others basic values
				else {
					$value = $this->buildBasicValue($property, $value);
				}
			}
			elseif (is_array($value)) {
				$link = Link_Annotation::of($property);
				// object
				if ($link->isObject()) {
					$class_name       = $type->asString();
					$composite_object = $property->getAnnotation('component')->value ? $object : null;
					/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
					$value = $this->buildObjectValue(
						$class_name, $property->getValue($object), $value, $null_if_empty, $composite_object
					);
				}
				// collection
				elseif ($link->isCollection()) {
					$class_name = $type->getElementTypeAsString();
					/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
					$value = $this->buildCollection(
						$class_name, $property->getValue($object), $value, $null_if_empty, $object
					);
				}
				// map or not-linked array of objects
				elseif ($type->isClass()) {
					if ($build->fast_add) {
						foreach ($value as $element_key => $element_string) {
							if ($element_string && $build->array[$property_name][$element_key]) {
								$value[$element_key] = $build->array[$property_name][$element_key];
							}
						}
					}
					$value = $this->buildMap($value, $type->getElementTypeAsString());
				}
			}
			// Fast_Add with id
			elseif (
				is_a($type->asString(), Fast_Add::class, true)
				&& isset($build->array[$id_property_name = ('id_' . $property_name)])
				&& $build->array[$id_property_name]
			) {
				$value = '';
			}
			// @output string
			elseif (
				isset($value)
				&& (
					($property->getAnnotation('output')->value === 'string')
					|| (strlen(trim($value)) && is_a($type->asString(), Fast_Add::class, true))
				)
			) {
				/** @see Fast_Add::fromString */
				$value = call_user_func([$type->asString(), 'fromString'], trim($value));
			}
		}
		// the property value is set only for official properties, if not default and not empty
		if (($value !== '') || !$type->isClass()) {
			if (
				!isset($object->$property_name) || ($value !== $object->$property_name) || $build->fast_add
			) {
				$object->$property_name = $value;
			}
		}
		if (
			$property->getAnnotation('empty_check')->value
			&& !$property->isValueEmptyOrDefault($value)
		) {
			$is_null = false;
		}
		return $is_null;
	}

	//--------------------------------------------------------------------------- buildSimpleProperty
	/**
	 * Builds a simple-name property (no DOT)
	 *
	 * @param $build         Object_Builder_Array_Tool
	 * @param $property_name string
	 * @param $value         mixed
	 * @throws User_Error_Exception
	 */
	private function buildSimpleProperty(
		Object_Builder_Array_Tool $build, string $property_name, mixed $value
	) {
		$asterisk = $this->extractAsterisk($property_name);
		$property = $this->properties[$property_name] ?? null;
		if (
			!$property
			&& (str_ends_with($property_name, '_'))
			&& isset($this->properties[substr($property_name, 0, -1)])
		) {
			$build->fast_add = true;
			$property        = $this->properties[substr($property_name, 0, -1)];
		}
		if (str_starts_with($property_name, 'id_')) {
			if (
				!$this->buildIdProperty($build->object, $property_name, $value, $build->null_if_empty)
				&& (
					($build->ignore_property_name !== $property_name)
					|| !isset($build->search[substr($property_name, 3)])
				)
			) {
				$build->is_null = false;
			}
		}
		elseif (($property_name !== 'id') && !isset($property)) {
			if (is_null($this->ignore_unknown_properties)) {
				$build->object->$property_name = $value;
			}
			elseif (!$this->ignore_unknown_properties) {
				trigger_error(
					'Unknown property ' . $this->class->name . '::$' . $property_name, E_USER_ERROR
				);
			}
		}
		elseif (!($property && $this->buildProperty($build, $property, $value))) {
			if (!$property || $property->getAnnotation('empty_check')->value) {
				$build->is_null = false;
			}
		}
		if ($asterisk) {
			$build->read_properties[$property_name] = $value;
		}
		$build->fast_add = false;
	}

	//-------------------------------------------------------------------------------- buildSubObject
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object        object
	 * @param $property      Reflection_Property
	 * @param $value         mixed
	 * @param $null_if_empty boolean
	 * @return boolean
	 * @throws User_Error_Exception
	 */
	private function buildSubObject(
		object $object, Reflection_Property $property, mixed $value, bool $null_if_empty
	) : bool
	{
		$is_null       = $null_if_empty;
		$property_name = $property->name;
		$type          = $property->getType();
		if (!isset($this->builders[$property_name])) {
			$this->builders[$property_name] = new Object_Builder_Array(
				$type->getElementTypeAsString(), $this->from_form
			);
		}
		$builder = $this->builders[$property_name];
		if ($type->isMultiple()) {
			$is_null = $this->buildSubObjectMultiple(
				$object, $property_name, $value, $null_if_empty, $builder
			);
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
			$sub_object = $property->getValue($object);
			$value      = $builder->build($value, $sub_object, $null_if_empty) ?: $sub_object;
			if (isset($value)) {
				$object->$property_name = $value;
				$is_null                = false;
			}
		}
		return $is_null;
	}

	//------------------------------------------------------------------------ buildSubObjectMultiple
	/**
	 * @param $builder       Object_Builder_Array
	 * @param $null_if_empty boolean
	 * @param $object        object
	 * @param $property_name string
	 * @param $value         mixed
	 * @return boolean
	 * @throws User_Error_Exception
	 */
	private function buildSubObjectMultiple(
		object $object, string $property_name, mixed $value, bool $null_if_empty,
		Object_Builder_Array $builder
	) : bool
	{
		$is_null = $null_if_empty;
		if (is_array($value)) {
			// keys are numeric : multiple values case
			foreach ($value as $key => $element) {
				if (is_numeric($number = lParse($key, DOT, 1, false))) {
					$values[$number][rParse($key, DOT)] = $element;
				}
				else {
					unset($values);
					break;
				}
			}
			// single value case
			if (!isset($values)) {
				$values = [$value];
			}
			// build values
			foreach ($values as $element) {
				$element = $builder->build($element, null, $null_if_empty);
				if (isset($element)) {
					/** @noinspection PhpExpressionResultUnusedInspection Call property getter if exist */
					$object->$property_name;
					$object->$property_name[] = $element;
					$is_null = false;
				}
			}
		}
		return $is_null;
	}

	//------------------------------------------------------------------------------- buildSubObjects
	/**
	 * @param $build Object_Builder_Array_Tool
	 * @throws User_Error_Exception
	 */
	private function buildSubObjects(Object_Builder_Array_Tool $build)
	{
		foreach ($build->objects as $property_name => $value) {
			$property = $this->properties[$property_name];
			if (!$this->buildSubObject($build->object, $property, $value, $build->null_if_empty)) {
				$build->is_null = false;
			}
		}
	}

	//------------------------------------------------------------------------------- extractAsterisk
	/**
	 * @param $property_name string may end with a '*' : if so, this last character will be removed
	 * @return boolean true if there is an '*', false if not
	 */
	private function extractAsterisk(string &$property_name) : bool
	{
		if ($asterisk = str_ends_with($property_name, '*')) {
			$property_name = substr($property_name, 0, -1);
		}
		return $asterisk;
	}

	//------------------------------------------------------------------------------- getBuiltObjects
	/**
	 * Call this after calls to build() to get all objects list set by the built
	 *
	 * @return Built_Object[]
	 */
	public function getBuiltObjects() : array
	{
		return $this->built_objects;
	}

	//-------------------------------------------------------------------------------- initLinkObject
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $array  array
	 * @param $object ?object
	 * @return ?array
	 * @throws User_Error_Exception
	 */
	private function initLinkObject(array &$array, ?object &$object) : ?array
	{
		$link = Class_\Link_Annotation::of($this->class);
		if ($link->value) {
			$id_property_value = null;
			$linked_class_name = null;
			$link_properties   = $link->getLinkClass()->getUniqueProperties();
			$search            = [];
			foreach ($link_properties as $property) {
				$property_name = $property->getName();
				if (Dao::storedAsForeign($property)) {
					$id_property_name = 'id_' . $property_name;
					if (isset($array[$id_property_name])) {
						$search[$property_name] = $array[$id_property_name] ?: null;
					}
					$property_class_name = $property->getType()->asString();
					if (is_a($property_class_name, $link->value, true)) {
						$id_property_value = $array[$id_property_name] ?? null;
						$linked_class_name = $property_class_name;
						if (!isset($array[$id_property_name]) && !isset($array[$property_name])) {
							$linked_array = $array;
							foreach (array_keys($link_properties) as $link_property_name) {
								unset($linked_array[$link_property_name]);
								unset($linked_array['id_' . $link_property_name]);
							}
							$link_class_properties = $link->getLinkClass()->getLocalProperties();
							foreach (array_keys($link_class_properties) as $link_property_name) {
								unset($linked_array[$link_property_name]);
								unset($linked_array['id_' . $link_property_name]);
							}
							if ($linked_array) {
								$builder = new Object_Builder_Array($property_class_name, $this->from_form);
								if ($this->null_if_empty_sub_objects) {
									$builder->null_if_empty_sub_objects = true;
								}
								$array[$property_name] = $builder->build($linked_array);
							}
						}
					}
				}
				elseif (isset($array[$property_name])) {
					$search[$property_name] = $property->getType()->isDateTime()
						? Loc::dateToIso($array[$property_name])
						: $array[$property_name];
				}
			}
			if (count($search) >= count($link_properties)) {
				$object = Dao::searchOne($search, $this->class->name);
			}
			if ($id_property_value && !$object) {
				/** @noinspection PhpUnhandledExceptionInspection read object must be valid */
				$object = Builder::createClone(
					Dao::read($id_property_value, $linked_class_name), $this->class->name
				);
			}
			return $search;
		}
		return null;
	}

	//------------------------------------------------------------------------------------ initObject
	/**
	 * Initializes the object if not already set
	 * - if a data link identifier is set, read the object from the data link and remove it from
	 *   $array
	 * - if the object is a link class and the link class identifier properties values are set,
	 *   read the object from the data link
	 * - if the object is a Component with a known composite, initializes its composite object to
	 *   $this->composite
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $array  array  the source array
	 * @param $object ?object the object to complete (if set) or to build (if null)
	 *                This object is always set at the end of execution of initObject()
	 * @return ?array if read from a link object, this is the search properties that identify it
	 * @throws User_Error_Exception
	 */
	private function initObject(array &$array, ?object &$object) : ?array
	{
		if (!$object) {
			if (isset($array['id']) && $array['id']) {
				$object = Dao::read($array['id'], $this->class->name);
				// if the object has been removed from the database : we will create a new one
				if (!$object) {
					unset($array['id']);
				}
			}
			if (!(isset($array['id']) && $array['id'])) {
				$link_search = $this->initLinkObject($array, $object);
				if (!isset($object)) {
					/** @noinspection PhpUnhandledExceptionInspection Must be valid */
					$object = $this->class->newInstance();
				}
			}
			if (isset($array['id'])) {
				unset($array['id']);
			}
		}
		if ($this->composite && isA($object, Component::class)) {
			$object->setComposite($this->composite);
		}
		foreach ($this->class->getAnnotations('before_build_array') as $before) {
			call_user_func_array([$object, $before->value], [&$array]);
		}
		return $link_search ?? null;
	}

	//------------------------------------------------------------------------------------ readObject
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object          object
	 * @param $read_properties string[] properties names
	 * @return object
	 */
	public function readObject(object $object, array $read_properties) : object
	{
		$objects = Dao::search($read_properties, get_class($object));
		if (count($objects) > 1) {
			trigger_error(
				'Unique object not found' . SP . get_class($object) . SP . print_r($read_properties, true),
				E_USER_ERROR
			);
		}
		elseif ($objects) {
			$new_object = reset($objects);
			/** @noinspection PhpUnhandledExceptionInspection object */
			foreach ((new Reflection_Class($object))->getProperties() as $property) {
				$property_name = $property->name;
				if (isset($object->$property_name) && !isset($read_properties[$property->name])) {
					/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
					$property->setValue($new_object, $property->getValue($object));
				}
			}
			$object = $new_object;
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------- setClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 */
	public function setClass(string $class_name)
	{
		if ($this->started) {
			$this->stop();
		}
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		$this->class = new Reflection_Class(Builder::className($class_name));
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * @param $class_name string|null
	 */
	public function start(string $class_name = null)
	{
		if (isset($class_name)) {
			$this->setClass($class_name);
		}
		elseif ($this->started) {
			$this->stop();
		}
		$this->built_objects = [];
		$this->properties    = $this->class->getProperties();
		$this->started       = true;
	}

	//------------------------------------------------------------------------------------------ stop
	public function stop()
	{
		$this->started = false;
	}

}
