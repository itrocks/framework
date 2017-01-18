<?php
namespace ITRocks\Framework\Dao\Data_Link;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Option\Spreadable;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Storage_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\String_Class;

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
	 * @var array
	 */
	public $array;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Link_Class
	 */
	protected $class;

	//---------------------------------------------------------------------------------- $collections
	/**
	 * @var array
	 */
	public $collections;

	//-------------------------------------------------------------------------------------- $exclude
	/**
	 * Will exclude these properties (property names) from writing
	 *
	 * @var string[]
	 */
	protected $exclude = [];

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var Identifier_Map
	 */
	protected $link;

	//----------------------------------------------------------------------------------------- $maps
	/**
	 * @var array
	 */
	public $maps;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	protected $object;

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * @var array
	 */
	public $objects;

	//----------------------------------------------------------------------------------------- $only
	/**
	 * Will write only these properties (property names)
	 * If null, all properties but excluded and @store false will be written
	 *
	 * @var string[]
	 */
	protected $only = null;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Spreadable[]
	 */
	protected $options;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var array
	 */
	public $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 *
	 * @param $link    Identifier_Map
	 * @param $object  object
	 * @param $options Spreadable[]
	 */
	public function __construct(Identifier_Map $link, $object, array $options)
	{
		$this->link    = $link;
		$this->object  = $object;
		$this->options = $options;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return static
	 * @throws Exception
	 */
	public function build()
	{
		if (!$this->class) {
			$this->class = new Link_Class(get_class($this->object));
		}
		$link                = Class_\Link_Annotation::of($this->class);
		$table_columns_names = array_keys($this->link->getStoredProperties($this->class));
		$this->array         = [];
		$this->collections   = [];
		$this->maps          = [];
		$this->objects       = [];
		$this->properties    = [];
		$aop_getter_ignore   = Getter::ignore(true);
		$exclude_properties  = $link->value
			? array_keys((new Reflection_Class($link->value))->getProperties([T_EXTENDS, T_USE]))
			: [];
		/** @var $properties Reflection_Property[] */
		$properties = $this->class->accessProperties();
		$properties = Replaces_Annotations::removeReplacedProperties($properties);
		foreach ($properties as $property) {
			if (
				(!isset($this->only) || in_array($property->name, $this->only))
				&& !$property->isStatic()
				&& !in_array($property->name, $this->exclude)
				&& !in_array($property->name, $exclude_properties)
				&& !Store_Annotation::of($property)->isFalse()
			) {
				$property_name = $property->name;
				$value         = isset($this->object->$property_name)
					? $property->getValue($this->object)
					: null;
				if (is_null($value) && !Null_Annotation::of($property)->value) {
					$value = '';
				}
				// write a property that matches a stored property (a table column name)
				if (in_array($property_name, $table_columns_names)) {
					list($column_name, $value, $write_property) = $this->propertyTableColumnName(
						$property, $value, $aop_getter_ignore
					);
					if ($value !== self::DO_NOT_WRITE) {
						$this->array[$column_name] = $value;
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
				// write object
				elseif (
					Link_Annotation::of($property)->isObject()
					&& $property->getAnnotation('component')->value
				) {
					$this->objects[] = [$property, $value];
				}
			}
		}
		Getter::$ignore = $aop_getter_ignore;
		return $this;
	}

	//--------------------------------------------------------------------------------- propertyBasic
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return mixed
	 */
	protected function propertyBasic(Reflection_Property $property, $value)
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
					: json_encode($value);
			}
			$write_value = $values ? new String_Class($value) : $value;
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
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return string[]|null [mixed $property_name, mixed $value, Data_Link $dao]
	 */
	protected function propertyDao(Reflection_Property $property, $value)
	{
		if ($dao_name = $property->getAnnotation('dao')->value) {
			if (($dao = Dao::get($dao_name)) !== $this) {
				return [$property->name, $value, $dao];
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------ propertyMapValue
	/**
	 * @param $property Reflection_Property
	 * @param $values   array
	 * @return array $values
	 */
	protected function propertyMapValue(Reflection_Property $property, array &$values)
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
	 * @param $property          Reflection_Property
	 * @param $value             object
	 * @param $aop_getter_ignore boolean
	 */
	protected function propertyObject(
		Reflection_Property $property, $value, $aop_getter_ignore = false
	) {
		$class_name     = get_class($value);
		$element_type   = $property->getType()->getElementType();
		$id_column_name = 'id_' . $property->name;
		$value_class    = new Link_Class($class_name);
		$id_value       = (
			$value_class->getLinkedClassName()
			&& !Class_\Link_Annotation::of($element_type->asReflectionClass())->value
		) ? ('id_' . $value_class->getCompositeProperty()->name)
			: 'id';
		$this->object->$id_column_name = $this->link->getObjectIdentifier($value, $id_value);
		if (empty($this->object->$id_column_name)) {
			$aop_getter_ignore_back = Getter::ignore($aop_getter_ignore);
			if (!isset($value) || isA($element_type->asString(), $class_name)) {
				$this->object->$id_column_name = $this->link->getObjectIdentifier(
					$this->link->write($value, $this->options), $id_value
				);
			}
			else {
				$clone = Builder::createClone($value, $property->getType()->asString());
				$this->object->$id_column_name = $this->link->getObjectIdentifier(
					$this->link->write($clone, $this->options), $id_value
				);
				$this->link->replace($value, $clone, false);
			}
			Getter::ignore($aop_getter_ignore_back);
		}
	}

	//--------------------------------------------------------------------------------- propertyStore
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return string
	 */
	protected function propertyStore(Reflection_Property $property, $value)
	{
		$store = Store_Annotation::of($property);
		if ($store->isJson()) {
			$value = $this->valueToWriteArray($value, $this->options);
			if (isset($value) && !is_string($value)) {
				$value = json_encode($value);
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
	 * @param $property          Reflection_Property
	 * @param $value             mixed
	 * @param $aop_getter_ignore boolean
	 * @return array [string $storage_name, mixed $write_value, $write_property]
	 */
	protected function propertyTableColumnName(
		Reflection_Property $property, $value, $aop_getter_ignore
	) {
		$element_type   = $property->getType()->getElementType();
		$storage_name   = Storage_Annotation::of($property)->value;
		$write_property = null;
		// write basic
		if ($element_type->isBasic(false)) {
			$write_value = $this->propertyBasic($property, $value);
		}
		// write array or object into a @store gz/hex/string
		elseif (Store_Annotation::of($property)->value) {
			$write_value = $this->propertyStore($property, $value);
			if ($write_property = $this->propertyDao($property, $write_value)) {
				$write_value = '';
			}
		}
		// write object id if set or object if no id is set (new object)
		else {
			if (is_object($value)) {
				$this->propertyObject($property, $value, $aop_getter_ignore);
			}
			$id_column_name = 'id_' . $property->name;
			$storage_name   = 'id_' . $storage_name;
			$write_value =
				(Null_Annotation::of($property)->value && !isset($this->object->$id_column_name))
				? null
				: intval($this->object->$id_column_name);
		}
		return [$storage_name, $write_value, $write_property];
	}


	//-------------------------------------------------------------------------- setPropertiesFilters
	/**
	 * @param $class   Link_Class
	 * @param $only    string[]|null
	 * @param $exclude string[]
	 * @return static
	 */
	public function setPropertiesFilters(Link_Class $class, array $only = null, array $exclude)
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
	 * @return array
	 */
	protected function valueToWriteArray($value, array $options)
	{
		$array = [];
		if (is_object($value)) {
			// encode only stored data and map, not collection
			$object_to_write_array = (new Object_To_Write_Array($this->link, $value, $options))->build();
			$array = $object_to_write_array->array;
			$maps  = $object_to_write_array->maps;
			// JSON comes first, like it is done by serialize()
			$array = array_merge([Store_Annotation::JSON_CLASS => get_class($value)], $array);
			foreach ($maps as list($property, $values)) {
				/** @var $property Reflection_Property */
				foreach ($values as $key => $value) {
					if (Dao::getObjectIdentifier($value)) {
						$array[$property->name][$key] = Dao::getObjectIdentifier($value);
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
