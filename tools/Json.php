<?php
namespace ITRocks\Framework\Tools;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
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
	 * @param $class_name     string
	 * @return array|object
	 */
	public function decodeObject($encoded_string, $class_name = null)
	{
		return isset($class_name)
			? Builder::fromArray($class_name, json_decode($encoded_string, true))
			: json_decode($encoded_string);
	}

	//---------------------------------------------------------------------------------- encodeObject
	/**
	 * @param $object array|object
	 * @return string
	 */
	public function encodeObject($object)
	{
		return json_encode($object);
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
	protected function getUnduplicateNameFromObject($business_object)
	{
		return Dao::current()->storeNameOf(get_class($business_object))
			. SL
			. Dao::getObjectIdentifier($business_object);
	}

	//--------------------------------------------------------------------------- isBrowsableProperty
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isBrowsableProperty(Reflection_Property $property)
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
	protected function notBrowsableObjectToStdClass(stdClass $standard_object, $business_object)
	{
		$standard_object->id        = Dao::getObjectIdentifier($business_object);
		$standard_object->as_string = strval($business_object);
	}

	//---------------------------------------------------------------------- objectToStdClassInternal
	/**
	 * @param $standard_object   stdClass object resulting
	 * @param $business_object   object business object to transform
	 * @param $parent_tree       array array of unduplicate name for each object in calling hierarchy
	 *     $parent_tree[0] => unduplicate name for top most object (first object parsed)
	 *     $parent_tree[] => unduplicate name for collection or component property of direct above object
	 * @return boolean
	 * @throws Exception
	 */
	protected function objectToStdClassInternal(
		stdClass $standard_object, $business_object, $parent_tree = []
	) {
		$class      = new Reflection_Class($business_object);
		$properties = $class->getProperties([T_USE, T_EXTENDS, Reflection_Class::T_SORT]);
		// bloc to avoid "death kiss" (infinite loop), detect when a parent is exactly the same object
		$unduplicate_name = $this->getUnduplicateNameFromObject($business_object);
		if (in_array($unduplicate_name, $parent_tree)) return false;
		array_push($parent_tree, $unduplicate_name);
		foreach ($properties as $property) {
			if (
				$property->isVisible()
				&& $property->isPublic()
				&& !$property->isStatic()
				&& !$property->getAnnotation('composite')->value
			) {
				$name           = $property->name;
				$property_value = $property->getValue($business_object);
				$type           = $property->getType();
				if ($type->isMultiple()) {
					$array = [];
					if ($type->isMultipleString()) {
						//TODO: check with many values, if $property_value is still a string with comma or directly an array?
						$values = ($property_value !== '') ? explode(',', $property_value): null;
					}
					elseif ($property_value) {
						$values = $property_value;
					}
					if (isset($values)) {
						foreach($values as $value) {
							$array[] = $this->propertyToStdInternal(
								$property, $value, $type, $parent_tree
							);
						}
					}
					$standard_object->$name = $array;
				}
				else {
					$standard_object->$name = $this->propertyToStdInternal(
						$property, $property_value, $type, $parent_tree
					);
				}
			}
		}
		return true;
	}

	//------------------------------------------------------------------------- propertyToStdInternal
	/**
	 * Returns value transformed in a suitable format for json
	 *
	 * @param $property       Reflection_Property
	 * @param $value          mixed
	 * @param $type           Type
	 * @param $parent_tree    array array of unduplicate name for each object in calling hierarchy
	 *     $parent_tree[0] => unduplicate name for top most object (first object parsed)
	 *     $parent_tree[] => unduplicate name for collection or component property of direct above object
	 * @return mixed
	 * @throws Exception
	 */
	protected function propertyToStdInternal (
		Reflection_Property $property, $value, Type $type = null, $parent_tree = []
	) {

		if (!isset($type)) {
			$type = $property->getType();
		}
		if (is_array($value)) {
			throw new Exception('Bad argument given. Value should not be array');
		}
		if ($type->isStrictlyBasic() || $type->isMultipleString()) {
			return $value;
		}
		elseif ($type->isDateTime()) {
			if (($value instanceof Date_Time) && !($value instanceof Date_Time_Error)) {
				return $value->toISO();
			}
			else {
				return null;
			}
		}
		elseif ($type->isClass()) {
			if ($type->isStringable()) {
				return strval($value);
			}
			else {
				if (!$value) {
					return null;
				}
				else {
					$sub_object = new stdClass();

					// case we should expand
					if ($this->isBrowsableProperty($property)) {
						if (!$this->objectToStdClassInternal($sub_object, $value, $parent_tree)) {
							$this->notBrowsableObjectToStdClass($sub_object, $value);
						}
					}
					// case we do not expand
					else {
						$this->notBrowsableObjectToStdClass($sub_object, $value);
					}

					return $sub_object;
				}
			}
		}
		else {
			throw new Exception('Missing type case for Json::objectToStdClassInternal');
		}
	}

	//---------------------------------------------------------------------------------------- toJson
	/**
	 * @param $standard_object stdClass|array
	 * @param $options    integer options for json_encode
	 * @return string json encoded representation of object
	 */
	public function toJson($standard_object, $options = 0)
	{
		return json_encode($standard_object, $options);
	}

	//----------------------------------------------------------------------------------- toStdObject
	/**
	 * @param $business_object object of a business class
	 * @return stdClass representation of object with visible properties
	 * @throws Exception
	 */
	public function toStdObject($business_object)
	{
		$standard_object = new stdClass();
		$this->objectToStdClassInternal($standard_object, $business_object);
		return $standard_object;
	}

}
