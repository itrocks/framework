<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Json\Exception;
use ReflectionException;
use stdClass;

/**
 * Json utility class
 *
 * @todo add method fromJson()
 */
class Json
{

	//---------------------------------------------------------------------------------- decodeObject
	/**
	 * @param $encoded_string string
	 * @param $class_name     string|null
	 * @return array|object
	 * @throws ReflectionException
	 */
	public function decodeObject(string $encoded_string, string $class_name = null) : array|object
	{
		return isset($class_name)
			? Builder::fromArray($class_name, json_decode($encoded_string, true))
			: json_decode($encoded_string);
	}

	//--------------------------------------------------------------------------------- decodeObjects
	/**
	 * @param $encoded_string string
	 * @param $class_name     string|null
	 * @return array|object[]
	 * @throws ReflectionException
	 */
	public function decodeObjects(string $encoded_string, string $class_name = null) : array
	{
		$data = json_decode($encoded_string, true);
		if (!isset($class_name)) {
			return $data;
		}
		foreach ($data as $key => $object) {
			$data[$key] = Builder::fromArray($class_name, $object);
		}
		return $data;
	}

	//---------------------------------------------------------------------------------- encodeObject
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object array|object
	 * @return string
	 */
	public function encodeObject(array|object $object) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return jsonEncode($object);
	}

	//------------------------------------------------------------------ getUnduplicateNameFromObject
	/**
	 * Generate a light string representation of object to detect duplicate run through same instance
	 *
	 * Class name is too discriminant because it gives a different string for two objects herited
	 * from same parent and with same database link.
	 * Return format : DatabaseStoreName/<object identifier>
	 *
	 * @example
	 * $business_object = Sfkgroup\Website\API\Agency {id:1625 ...}
	 * $business_object = Sfkgroup\Agency {id:1625 ...}
	 * twice return same string : "agencies/1625"
	 * @param $business_object object business object to convert in his unduplicate representation
	 * @return string
	 */
	protected function getUnduplicateNameFromObject(object $business_object) : string
	{
		return Dao::current()->storeNameOf($business_object)
			. SL
			. Dao::getObjectIdentifier($business_object);
	}

	//--------------------------------------------------------------------------- isBrowsableProperty
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isBrowsableProperty(Reflection_Property $property) : bool
	{
		return $property->getAnnotation('component')->value
			|| Link_Annotation::of($property)->isCollection();
	}

	//------------------------------------------------------------------ notBrowsableObjectToStdClass
	/**
	 * Smalest object's representation
	 *
	 * @param $standard_object stdClass object resulting
	 * @param $business_object object   object to convert to stdClass
	 */
	protected function notBrowsableObjectToStdClass(
		stdClass $standard_object, object $business_object
	) : void
	{
		$standard_object->id        = Dao::getObjectIdentifier($business_object);
		$standard_object->as_string = strval($business_object);
	}

	//---------------------------------------------------------------------- objectToStdClassInternal
	/**
	 * @noinspection PhpDocMissingThrowsInspection ReflectionException
	 * @param $standard_object stdClass object resulting
	 * @param $business_object object business object to transform
	 * @param $parent_tree     array array of unduplicate name for each object in calling hierarchy
	 *     $parent_tree[0] => unduplicate name for top most object (first object parsed)
	 *     $parent_tree[]  => unduplicate name for collection or component property of direct above object
	 * @return boolean
	 * @throws Exception
	 */
	protected function objectToStdClassInternal(
		stdClass $standard_object, object $business_object, array $parent_tree = []
	) : bool
	{
		/** @noinspection PhpUnhandledExceptionInspection An object is always valid */
		$class      = new Reflection_Class($business_object);
		$properties = $class->getProperties([T_EXTENDS, T_USE, Reflection_Class::T_SORT]);
		// bloc to avoid "death kiss" (infinite loop), detect when a parent is exactly the same object
		$unduplicate_name = $this->getUnduplicateNameFromObject($business_object);
		if (in_array($unduplicate_name, $parent_tree)) return false;
		$parent_tree[] = $unduplicate_name;
		foreach ($properties as $property) {
			if ($this->shouldExportProperty($property)) {
				$name                   = $property->name;
				/** @noinspection PhpUnhandledExceptionInspection $property comes from $business_object */
				$property_value         = $property->getValue($business_object);
				$type                   = $property->getType();
				$standard_object->$name = $this->propertyToStdInternal(
					$property, $property_value, $type, $parent_tree
				);
			}
		}
		return true;
	}

	//------------------------------------------------------------------------- propertyToStdInternal
	/**
	 * Returns value transformed in a suitable format for json
	 *
	 * @param $property    Reflection_Property
	 * @param $value       mixed
	 * @param $type        Type|null
	 * @param $parent_tree array array of unduplicate name for each object in calling hierarchy
	 *     $parent_tree[0] => unduplicate name for top most object (first object parsed)
	 *     $parent_tree[]  => unduplicate name for collection or component property of direct above object
	 * @return mixed
	 * @throws Exception
	 */
	protected function propertyToStdInternal(
		Reflection_Property $property, mixed $value, Type $type = null, array $parent_tree = []
	) : mixed
	{
		if (!isset($type)) {
			$type = $property->getType();
		}
		if ($type->isStrictlyBasic() || $type->isMultipleString()) {
			return $value;
		}
		if ($type->isDateTime()) {
			if (($value instanceof Date_Time) && !($value instanceof Date_Time_Error)) {
				return $value->toISO();
			}
			return null;
		}
		if (!$type->isClass()) {
			throw new Exception('Missing type case for Json::propertyToStdInternal');
		}
		if ($type->isStringable()) {
			return strval($value);
		}
		if (!$value) {
			return null;
		}
		$browse = $this->isBrowsableProperty($property);
		if (!$type->isMultiple()) {
			return $this->subObjectToStdClassInternal($browse, $value, $parent_tree);
		}
		$array = [];
		foreach ($value as $business_object) {
			$array[] = $this->subObjectToStdClassInternal($browse, $business_object, $parent_tree);
		}
		return $array;
	}

	//-------------------------------------------------------------------------- shouldExportProperty
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	public function shouldExportProperty(Reflection_Property $property)
	{
		return $property->isVisible()
			&& $property->isPublic()
			&& !$property->isStatic()
			&& !Composite::of($property)?->value;
	}

	//------------------------------------------------------------------- subObjectToStdClassInternal
	/**
	 * @param $browse          boolean
	 * @param $business_object mixed
	 * @param $parent_tree     array of unduplicate name for each object in calling hierarchy
	 *     $parent_tree[0] => unduplicate name for top most object (first object parsed)
	 *     $parent_tree[]  => unduplicate name for collection or component property of direct above object
	 * @return stdClass
	 * @throws Exception
	 */
	protected function subObjectToStdClassInternal(
		bool $browse, mixed $business_object, array $parent_tree
	) : stdClass
	{
		$sub_object = new stdClass();
		// case we should expand
		if ($browse) {
			if (!$this->objectToStdClassInternal($sub_object, $business_object, $parent_tree)) {
				$this->notBrowsableObjectToStdClass($sub_object, $business_object);
			}
		}
		// case we do not expand
		else {
			$this->notBrowsableObjectToStdClass($sub_object, $business_object);
		}
		return $sub_object;
	}

	//---------------------------------------------------------------------------------------- toJson
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $standard_object array|object
	 * @param $options         integer options for json_encode
	 * @return string json encoded representation of object
	 */
	public function toJson(array|object $standard_object, int $options = 0) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return jsonEncode($standard_object, $options);
	}

	//----------------------------------------------------------------------------------- toStdObject
	/**
	 * @param $business_object object of a business class
	 * @return stdClass representation of object with visible properties
	 * @throws Exception
	 */
	public function toStdObject(object $business_object) : stdClass
	{
		$standard_object = new stdClass();
		$this->objectToStdClassInternal($standard_object, $business_object);
		return $standard_object;
	}

}
