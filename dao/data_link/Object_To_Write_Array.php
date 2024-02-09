<?php
namespace ITRocks\Framework\Dao\Data_Link;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Option\Spreadable;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Getter_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Stringable;

/**
 * Object to write array
 *
 * Protected properties are inputs, set by __construct() and setPropertiesFilters()
 * Public properties are outputs, result of build()
 */
class Object_To_Write_Array
{

	//---------------------------------------------------------------------------------- DO_NOT_WRITE
	const DO_NOT_WRITE = 'ITRocks-do-not-write';

	//---------------------------------------------------------------------------------------- $array
	/**
	 * The resulting write array associates the database table's column name to its stored value
	 *
	 * @var array [string $column_name => mixed $value]
	 */
	public array $array;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * The class of the written object we want to write properties from
	 *
	 * On @link classes : Object_To_Write_Array may be called for each @link class, then for the
	 * linked class, to restrict the written properties (one write per link class).
	 *
	 * @var Link_Class
	 */
	protected Link_Class $class;

	//---------------------------------------------------------------------------------- $collections
	/**
	 * The resulting collections of objects (matches @link Collection properties of $object)
	 *
	 * @var array [Reflection_Property $property, object[] $value]
	 */
	public array $collections;

	//-------------------------------------------------------------------------------------- $exclude
	/**
	 * Will exclude these properties (property names) from writing
	 *
	 * @var string[]
	 */
	protected array $exclude = [];

	//-------------------------------------------------------------------------------- $json_encoding
	/**
	 * When encoding an object / sub-objects from an array using json, this is true
	 * Enable recursive json encoding of sub-objects
	 *
	 * @var boolean
	 */
	protected bool $json_encoding;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * The data link used for write
	 *
	 * @var Identifier_Map
	 */
	protected Identifier_Map $link;

	//----------------------------------------------------------------------------------------- $maps
	/**
	 * The resulting maps of objects (matches @link Map properties of $object)
	 *
	 * @var array [Reflection_Property $property, object[] $value]
	 */
	public array $maps;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The written object : we convert values to write from it
	 *
	 * @var object
	 */
	protected object $object;

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * The resulting component objects (matches @component @link Object properties of $object)
	 *
	 * @var array
	 */
	public array $objects;

	//----------------------------------------------------------------------------------------- $only
	/**
	 * Will write only these properties (property names)
	 * If empty, all properties but excluded and #Store(false) will be written
	 *
	 * @var string[]
	 */
	protected array $only = [];

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Spreadable[]
	 */
	protected array $options;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var array
	 */
	public array $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 *
	 * @param $link          Identifier_Map
	 * @param $object        object
	 * @param $options       Spreadable[]
	 * @param $json_encoding boolean
	 */
	public function __construct(
		Identifier_Map $link, object $object, array $options, bool $json_encoding = false
	) {
		$this->json_encoding = $json_encoding;
		$this->link          = $link;
		$this->object        = $object;
		$this->options       = $options;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build output properties values (all public properties)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return $this
	 */
	public function build() : static
	{
		if (!isset($this->class)) {
			/** @noinspection PhpUnhandledExceptionInspection valid object */
			$this->class = new Link_Class($this->object);
		}
		$link                = Class_\Link_Annotation::of($this->class);
		$table_columns_names = array_keys($this->link->getStoredProperties($this->class));
		$this->array         = [];
		$this->collections   = [];
		$this->maps          = [];
		$this->objects       = [];
		$this->properties    = [];
		/** @noinspection PhpUnhandledExceptionInspection link value must be a valid class */
		$exclude_properties  = $link->value
			? array_keys((new Reflection_Class($link->value))->getProperties([T_EXTENDS, T_USE]))
			: [];
		$properties        = $this->class->getProperties();
		$properties        = Replaces_Annotations::removeReplacedProperties($properties);
		$aop_getter_ignore = Getter::$ignore;
		foreach ($properties as $property) {
			if (
				(empty($this->only) || in_array($property->name, $this->only, true))
				&& !$property->isStatic()
				&& !in_array($property->name, $this->exclude, true)
				&& !in_array($property->name, $exclude_properties, true)
				&& !Store_Annotation::of($property)->isFalse()
				&& !($this->json_encoding && $property->getAnnotation('composite')->value)
			) {
				$property_name     = $property->name;
				$getter_annotation = Getter_Annotation::of($property);
				if (
					$getter_annotation->value
					&& !is_a($getter_annotation->getReflectionMethod()->class, Getter::class, true)
				) {
					// call @getter (not really : only isset is called. Real @getter call may cause problems)
					// TODO isset now calls getter in order to get a real data isset result. Problems ?
					$is_property_set = isset($this->object->$property_name);
				}
				else {
					// do not call @link implicit getters
					Getter::$ignore  = true;
					$is_property_set = isset($this->object->$property_name);
					Getter::$ignore  = $aop_getter_ignore;
				}
				/** @noinspection PhpUnhandledExceptionInspection $property is valid for $object */
				$value = $is_property_set ? $property->getValue($this->object) : null;
				if (is_null($value) && !Null_Annotation::of($property)->value) {
					$value = '';
				}
				// write a property that matches a stored property (a table column name)
				if (in_array($property_name, $table_columns_names, true)) {
					[$column_name, $value, $write_property, $class_name] = $this->propertyTableColumnName(
						$property, $value
					);
					if ($value !== self::DO_NOT_WRITE) {
						$this->array[$column_name] = $value;
						if ($class_name) {
							$this->array[$column_name . '_class'] = $class_name;
						}
					}
					if (isset($write_property)) {
						$this->properties[] = $write_property;
					}
				}
				// write collection
				elseif (is_array($value) && Link_Annotation::of($property)->isCollection()) {
					$this->collections[] = [$property, $value];
				}
				// write map
				elseif (is_array($value) && Link_Annotation::of($property)->isMap()) {
					$this->maps[] = [$property, $this->propertyMapValue($property, $value)];
				}
				elseif (is_array($value) && $this->json_encoding) {
					[$column_name, $value]     = $this->propertyTableColumnName($property, $value);
					$this->array[$column_name] = $value;
				}
				// write object
				elseif (
					Link_Annotation::of($property)->isObject()
					&& $property->getAnnotation('component')->value
				) {
					$this->objects[] = [$property, $value];
				}
			}
		}
		return $this;
	}

	//--------------------------------------------------------------------------------- propertyBasic
	/**
	 * Build data to be written for a property that has a basic (Type::isBasic) type
	 *
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return mixed
	 */
	protected function propertyBasic(Reflection_Property $property, mixed $value) : mixed
	{
		// $write_value is set to null for the IDE, but will be replaced on every cases
		$write_value = null;
		if (
			$property->getType()->getElementType()->isString()
			&& Store_Annotation::of($property)->is([Store_Annotation::GZ, Store_Annotation::HEX])
		) {
			if (Store_Annotation::of($property)->isGz()) {
				$value = gzdeflate($value);
			}
			$will_hex = true;
		}
		else {
			$values = Values_Annotation::of($property)->values();
			if (is_array($value)) {
				$value = ($property->getType()->isMultipleString() && $values)
					? join(',', $value)
					: $this->valueToWriteArray($value, $this->options);
			}
			$write_value = $value;
		}
		if ($write_property = $this->propertyDao($property, $value)) {
			$write_value        = '';
			$this->properties[] = $write_property;
			unset($will_hex);
		}
		if (isset($will_hex)) {
			$write_value = strlen($value)
				? ('X' . Q . bin2hex($value) . Q)
				: self::DO_NOT_WRITE;
		}
		return $write_value;
	}

	//----------------------------------------------------------------------------------- propertyDao
	/**
	 * Check if the property value has to be stored using an alternative data link (@dao)
	 *
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return ?string[] [mixed $property_name, mixed $value, Data_Link $data_link]
	 */
	protected function propertyDao(Reflection_Property $property, mixed $value) : ?array
	{
		if ($dao_identifier = $property->getAnnotation('dao')->value) {
			if (($data_link = Dao::get($dao_identifier)) !== $this->link) {
				return [$property->name, $value, $data_link];
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------ propertyMapValue
	/**
	 * For @link Map properties values : each value that is not an object is an identifier to the
	 * final object : read it and replace the final value.
	 *
	 * @param $property Reflection_Property
	 * @param $values   array
	 * @return array $values
	 */
	protected function propertyMapValue(Reflection_Property $property, array &$values) : array
	{
		$value_class_name = $property->getType()->getElementTypeAsString();
		foreach ($values as $key => $value) {
			if (!is_object($value)) {
				$value = Dao::read($value, $value_class_name);
				if (isset($value)) {
					$values[$key] = $value;
				}
				else {
					unset($values[$key]);
				}
			}
		}
		return $values;
	}

	//-------------------------------------------------------------------------------- propertyObject
	/**
	 * Build data to be written for a property value that has is an object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @param $value    object
	 */
	protected function propertyObject(Reflection_Property $property, object $value) : void
	{
		$class_name     = get_class($value);
		$element_type   = $property->getType()->getElementType();
		$id_column_name = 'id_' . $property->name;
		/** @noinspection PhpUnhandledExceptionInspection Called for a valid object only */
		$value_class = new Link_Class($class_name);
		$id_value    = (
			$value_class->getLinkedClassName()
			&& !Class_\Link_Annotation::of($element_type->asReflectionClass())->value
		) ? ('id_' . $value_class->getCompositeProperty()->name)
			: 'id';
		$this->object->$id_column_name = $this->link->getObjectIdentifier($value, $id_value);
		if (empty($this->object->$id_column_name)) {
			if (!isset($value) || isA($element_type->asString(), $class_name)) {
				$this->object->$id_column_name = $this->link->getObjectIdentifier(
					$this->link->write($value, $this->options), $id_value
				);
			}
			else {
				$class_name = $property->getType()->asString();
				/** @noinspection PhpUnhandledExceptionInspection only valid types allowed */
				$clone = Builder::createClone($value, $class_name);
				/** @noinspection PhpUnhandledExceptionInspection only valid types allowed */
				$value_class = new Link_Class($class_name);
				$id_value    = (
					$value_class->getLinkedClassName()
					&& !Class_\Link_Annotation::of($element_type->asReflectionClass())->value
				) ? ('id_' . $value_class->getCompositeProperty()->name)
					: 'id';
				$this->object->$id_column_name = $this->link->getObjectIdentifier(
					$this->link->write($clone, $this->options), $id_value
				);
				$this->link->replace($value, $clone, false);
			}
		}
	}

	//--------------------------------------------------------------------------- propertyStoreString
	/**
	 * Value to be stored as string : change an array / object to string when it has a @store option
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return string
	 */
	protected function propertyStoreString(Reflection_Property $property, mixed $value) : string
	{
		$store = Store_Annotation::of($property);
		if ($store->isJson() || $store->isSerialize()) {
			if (is_object($value) && ($write = (new Call_Stack)->getObject(Write::class))) {
				$write->beforeWrite($value, $this->options, Write::BEFORE_WRITE);
				$write->beforeWriteComponents($value, $this->options, Write::BEFORE_WRITE);
			}
			if (isset($value) && $store->isSerialize()) {
				$value = serialize($value);
			}
			else {
				$value = $this->valueToWriteArray($value, $this->options);
				if (isset($value) && !is_string($value)) {
					/** @noinspection PhpUnhandledExceptionInspection */
					$value = jsonEncode($value);
				}
			}
		}
		else {
			$value = is_array($value) ? serialize($value) : strval($value);
		}
		if ($store->isGz()) {
			$value = 'X' . Q . bin2hex(gzdeflate($value)) . Q;
		}
		elseif ($store->isHex()) {
			$value = 'X' . Q . bin2hex($value) . Q;
		}
		return $value;
	}

	//----------------------------------------------------------------------- propertyTableColumnName
	/**
	 * Build the value to be written for a property linked to a table column
	 * (it must not have @store false, nor be a collection, map or component object)
	 *
	 * @param $property Reflection_Property The property
	 * @param $value    mixed The value of the property to be written
	 * @return array [string $storage_name, mixed $write_value, $write_property, $class_name]
	 */
	protected function propertyTableColumnName(Reflection_Property $property, mixed $value) : array
	{
		$class_name             = null;
		$element_type           = $property->getType()->getElementType();
		$storage_name           = Store_Name_Annotation::of($property)->value;
		$store_annotation_value = Store_Annotation::of($property)->value;
		$write_property         = null;
		// write basic but test store as json too
		if ($element_type->isBasic(false) && ($store_annotation_value !== Store_Annotation::JSON)) {
			$write_value = $this->propertyBasic($property, $value);
		}
		// write array or object into a @store gz/hex/string
		elseif ($store_annotation_value) {
			$write_value = $this->propertyStoreString($property, $value);
			if ($write_property = $this->propertyDao($property, $write_value)) {
				$write_value = '';
			}
		}
		// return of value for not-linked array property value using json encoding
		elseif ($this->json_encoding && is_array($value)) {
			$write_value = $this->valueToWriteArray($value, $this->options);
		}
		// prepare Date_Time for json encoding
		elseif ($this->json_encoding && ($value instanceof Date_Time)) {
			$value_class_name = Builder::current()->sourceClassName(get_class($value));
			$write_value      = [
				Store_Annotation::JSON_CLASS     => $value_class_name,
				Store_Annotation::JSON_CONSTRUCT => $value->toISO()
			];
		}
		elseif ($element_type->isMixed()) {
			$write_value = $value;
		}
		// write object id if set or object if no id is set (new object)
		else {
			if (is_object($value)) {
				$this->propertyObject($property, $value);
			}
			$id_column_name = 'id_' . $property->name;
			$storage_name   = 'id_' . $storage_name;
			$write_value    =
				(!isset($this->object->$id_column_name) && Null_Annotation::of($property)->value)
				? null
				: intval($this->object->$id_column_name);
			if ((is_object($value) || is_int($write_value)) && $element_type->isAbstractClass()) {
				$class_column_name = $id_column_name . '_class';
				if (isset($this->object->$class_column_name) && $this->object->$class_column_name) {
					$class_name = $this->object->$class_column_name;
				}
				elseif (is_object($value)) {
					$class_name = Builder::current()->sourceClassName(get_class($value));
				}
			}
		}
		return [$storage_name, $write_value, $write_property, $class_name];
	}

	//-------------------------------------------------------------------------- setPropertiesFilters
	/**
	 * Set input data not set by __construct : $class, $only and $exclude
	 *
	 * @param $class   Link_Class The class of the written object we want to write properties from
	 * @param $only    string[] Will write only these properties : each value is a property name
	 * @param $exclude string[] Will exclude these properties from writing : each value is a name
	 * @return $this
	 */
	public function setPropertiesFilters(Link_Class $class, array $only, array $exclude) : static
	{
		$this->class   = $class;
		$this->exclude = $exclude;
		$this->only    = $only;
		return $this;
	}

	//----------------------------------------------------------------------------- valueToWriteArray
	/**
	 * Prepare a property value for JSON encode
	 *
	 * @param $value   mixed The value of a property
	 * @param $options Spreadable[] Spread options
	 * @return mixed
	 */
	protected function valueToWriteArray(mixed $value, array $options) : mixed
	{
		$array = [];
		if (is_object($value)) {
			// encode only stored data and map, not collection
			$object_to_write_array = (new Object_To_Write_Array($this->link, $value, $options, true))
				->build();
			$array       = $object_to_write_array->array;
			$collections = $object_to_write_array->collections;
			$maps        = $object_to_write_array->maps;
			// JSON comes first, like it is done by serialize()
			$value_class_name = Builder::current()->sourceClassName(get_class($value));
			$array            = array_merge([Store_Annotation::JSON_CLASS => $value_class_name], $array);
			foreach ($array as $key => $value) {
				if (is_object($value)) {
					if ($identifier = Dao::getObjectIdentifier($value)) {
						$value = $identifier;
					}
					elseif ($value instanceof Stringable) {
						$value = strval($value);
					}
					else {
						$value = $this->valueToWriteArray($value, $options);
						unset($value[Store_Annotation::JSON_CLASS]);
					}
					$array[$key] = $value;
				}
			}
			foreach ($collections as [$property, $values]) {
				/** @var $property Reflection_Property */
				foreach ($values as $key => $value) {
					$element = $this->valueToWriteArray($value, $options);
					unset($element[Store_Annotation::JSON_CLASS]);
					$array[$property->name][$key] = $element;
				}
			}
			foreach ($maps as [$property, $values]) {
				/** @var $property Reflection_Property */
				foreach ($values as $key => $value) {
					if ($identifier = Dao::getObjectIdentifier($value)) {
						$array[$property->name][$key] = $identifier;
					}
					else {
						$element = $this->valueToWriteArray($value, $options);
						unset($element[Store_Annotation::JSON_CLASS]);
						$array[$property->name][$key] = $element;
					}
				}
			}
		}
		elseif (is_array($value)) {
			foreach ($value as $key => $sub_value) {
				$array[$key] = $this->valueToWriteArray($sub_value, $options);
			}
		}
		else {
			$array = $value;
		}
		return $array;
	}

}
