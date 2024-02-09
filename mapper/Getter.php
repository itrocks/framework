<?php
namespace ITRocks\Framework\Mapper;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Sql\Builder\Link_Property_Name;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Date_Time_Error;
use ITRocks\Framework\Tools\Stringable;

/**
 * Getter default methods are common getters for Dao linked objects
 */
abstract class Getter
{

	//--------------------------------------------------------------------------------------- $ignore
	/**
	 * @var boolean
	 */
	public static bool $ignore = false;

	//------------------------------------------------------------------------- getAbstractCollection
	/**
	 * Gets a collection of objects which class is abstract.
	 * - Use Dependency to get all possible classes
	 * - Get objects from each possible class
	 * - Return all of them
	 *
	 * @param $class_name string
	 * @param $object     object
	 * @param $property   Reflection_Property|string|null
	 * @return object[]
	 */
	private static function getAbstractCollection(
		string $class_name, object $object, Reflection_Property|string $property = null
	) : array
	{
		$objects = [];
		$class_names = self::getFinalClasses($class_name);
		foreach ($class_names as $class_name) {
			$stored  = null;
			$objects = array_merge(
				$objects, self::getCollection($stored, $class_name, $object, $property)
			);
		}
		return $objects;
	}

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Generic getter for getting all objects of a given class
	 *
	 * @param $stored            object[]|null
	 * @param $element_type_name string|null
	 * @return object[]
	 */
	public static function & getAll(array &$stored = null, string $element_type_name = null) : array
	{
		if (!(self::$ignore || isset($stored))) {
			$stored = Dao::readAll($element_type_name, Dao::sort());
		}
		return $stored;
	}

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Generic getter for a collection of objects
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $stored     Component[]|string|null Actual value of the property
	 *                    (will be returned if not null)
	 * @param $class_name string|null Class for each collection's object
	 * @param $object     object|null Parent object
	 * @param $property   Reflection_Property|string|null Parent property (or property name).
	 *                    Recommended but can be omitted if foreign class is a Component
	 * @return object[]
	 */
	public static function & getCollection(
		array|string &$stored = null, string $class_name = null, object $object = null,
		Reflection_Property|string $property = null
	) : array
	{
		// TODO JSON will work only if $property is set. Should add string / null case
		if (
			!self::$ignore
			&& ($property instanceof Reflection_Property)
			&& Store_Annotation::of($property)->isJson()
		) {
			if (isset($stored) && is_string($stored)) {
				$objects_arrays = json_decode($stored, true);
				$stored         = [];
				if ($objects_arrays) {
					foreach ($objects_arrays as $key => $object_array) {
						$stored[$key] = static::schemaDecode($object_array, $property);
					}
				}
			}
		}
		elseif (!(self::$ignore || isset($stored))) {
			if (Dao::getObjectIdentifier($object)) {
				$class_name = Builder::className($class_name);
				if ((new Type($class_name))->isAbstractClass()) {
					$stored = self::getAbstractCollection($class_name, $object, $property);
				}
				else {
					$search_element = [];
					$is_component   = isA($class_name, Component::class);
					if ($property) {
						if (!($property instanceof Reflection_Property)) {
							/** @noinspection PhpUnhandledExceptionInspection Need valid $property of $object */
							$property = new Reflection_Property($object, $property);
						}
						$property_name = Foreign_Annotation::of($property)->value;
						$dao           = Dao::get($property->getAnnotation('dao')->value);
					}
					else {
						$dao           = Dao::current();
						$property_name = null;
					}
					if ($is_component) {
						/** @noinspection PhpUnhandledExceptionInspection Must be valid */
						/** @noinspection PhpUndefinedMethodInspection $is_component */
						/** @see Component::getCompositeProperties */
						$composite_properties = $class_name::getCompositeProperties($object, $property_name);
						foreach ($composite_properties as $composite_property) {
							$search_element[$composite_property->name] = $object;
						}
						/** @noinspection PhpUnhandledExceptionInspection already verified */
						$link_properties_names = (new Link_Class($class_name))->getUniquePropertiesNames();
						$options               = [Dao::sort()];
						if ($property_name) {
							$options[] = new Link_Property_Name($property_name);
						}
						if ($link_properties_names) {
							$options[] = Dao::key($link_properties_names);
						}
						// some mappers (e.g. User\Has_Groups::groups) may loop : initialize the property value
						$property->setValue($object, []);
						$stored = $dao->search($search_element, $class_name, $options);
					}
					// when element class is not a component and a property name was found
					elseif ($property_name) {
						$search_element[$property_name] = $object;
						$stored = $dao->search($search_element, $class_name, Dao::sort());
					}
					else {
						trigger_error(
							'getCollection() must be called for a component foreign type'
							. ' or with a parent property name',
							E_USER_ERROR
						);
					}
					if ($stored && $is_component) {
						// $element->setComposite() is not used for optimization reason :
						// this should go as fast as it can
						/** @var $element Component */
						$element = reset($stored);
						/** @var $composite_properties Reflection_Property[] */
						$composite_properties = call_user_func(
							[get_class($element), 'getCompositeProperties'], $object
						);
						foreach ($stored as $element) {
							foreach ($composite_properties as $property) {
								$id_property = 'id_' . $property->name;
								if (intval($element->$id_property) === intval($object->id)) {
									$property->setValue($element, $object);
								}
							}
						}
					}
				}
			}
			if (!isset($stored)) {
				$stored = [];
			}
		}
		return $stored;
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using '@link DateTime' annotation
	 *
	 * @param $stored Date_Time|string|null
	 * @return Date_Time
	 */
	public static function & getDateTime(Date_Time|string|null &$stored) : Date_Time
	{
		if ($stored instanceof Date_Time) {
			return $stored;
		}
		if (is_null($stored)) {
			$stored = Date_Time::min();
			return $stored;
		}
		try {
			$stored = Date_Time::fromISO($stored);
		}
		catch (Exception) {
			$stored = Date_Time_Error::fromError($stored);
		}
		return $stored;
	}

	//------------------------------------------------------------------------------- getFinalClasses
	/**
	 * Gets final class names of an extensible class
	 * This uses Dependency cache
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return string[]
	 */
	private static function getFinalClasses(string $class_name) : array
	{
		/** @noinspection DuplicatedCode inspector bug (duplicated with css code) */
		$class_names = [];
		$search      = ['dependency_name' => $class_name, 'type' => Dependency::T_EXTENDS];
		foreach (Dao::search($search, Dependency::class) as $dependency) {
			/** @noinspection PhpUnhandledExceptionInspection $dependency must always be valid */
			/** @var $dependency Dependency */
			$class = new Reflection_Class($dependency->class_name);
			if (!$class->isAbstract()) {
				$class_names[$class->name] = $class->name;
			}
			$class_names = array_merge($class_names, self::getFinalClasses($class->name));
		}
		return $class_names;
	}

	//--------------------------------------------------------------------------------------- getLink
	/**
	 * Getter call shortcut : do automatically what is needed to call the appropriate getter using
	 * the property link annotation value
	 *
	 * You may call this from your getter that overrides a link annotation
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object        object the object
	 * @param $property_name string the property to get value of : must exist for the object class
	 * @return object|object[]|null
	 */
	public static function & getLink(object $object, string $property_name) : array|object|null
	{
		/** @noinspection PhpUnhandledExceptionInspection valid object property */
		$property   = new Reflection_Property($object, $property_name);
		$class_name = $property->getType()->getElementTypeAsString();
		$link       = Link_Annotation::of($property);
		switch ($link->value) {
			case Link_Annotation::ALL:
				return static::getAll($object->$property_name, $class_name);
			case Link_Annotation::COLLECTION:
				return static::getCollection($object->$property_name, $class_name, $object, $property);
			case Link_Annotation::DATETIME:
				return static::getDateTime($object->$property_name);
			case Link_Annotation::MAP:
				return static::getMap($object->$property_name, $object, $property);
			case Link_Annotation::OBJECT:
				return static::getObject($object->$property_name, $object, $property);
		}
		$null = null;
		return $null;
	}

	//---------------------------------------------------------------------------------------- getMap
	/**
	 * Generic getter for mapped objects
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $stored   object[]|null actual value of the property (will be returned if not null)
	 * @param $object   object|null   the parent object
	 * @param $property Reflection_Property|string|null the source property (or name) for map reading
	 * @return Component[]
	 */
	public static function & getMap(
		array &$stored = null, object $object = null, Reflection_Property|string $property = null
	) : array
	{
		if (self::$ignore || isset($stored)) {
			return $stored;
		}
		if (Dao::getObjectIdentifier($object)) {
			if (!($property instanceof Reflection_Property)) {
				/** @noinspection PhpUnhandledExceptionInspection needs valid $property of $object */
				$property = new Reflection_Property($object, $property);
			}
			$dao        = Dao::get($property->getAnnotation('dao')->value);
			$class_name = get_class($object);
			/** @noinspection PhpUnhandledExceptionInspection $class_name is a get_class() */
			$link_class        = new Link_Class($class_name);
			$linked_class_name = $link_class->getLinkedClassName();
			if ($linked_class_name) {
				/** @noinspection PhpUnhandledExceptionInspection valid $object & getCompositeProperty */
				$object     = $link_class->getCompositeProperty()->getValue($object);
				$class_name = $linked_class_name;
			}
			$element_type = $property->getType()->getElementType();
			$is_abstract  = $element_type->isAbstractClass();
			$sort         = $is_abstract ? Dao::sort(['id']) : Dao::sort();
			// some mappers (e.g. User\Has_Groups::groups) may loop : initialize the property value
			$property->setValue($object, []);
			$stored = $dao->search(
				[$class_name . '->' . $property->name => $object], $element_type->asString(), [$sort]
			);
			if ($is_abstract) {
				$sort->sortObjects($stored);
			}
		}
		else {
			$stored = [];
		}
		return $stored;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Generic getter for an object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $stored     mixed actual value of the object, or identifier to an object, or null
	 * @param $class_name string the object class name
	 * @param $object     object|null the parent object
	 * @param $property   Reflection_Property|string|null the parent property
	 * @return ?object
	 */
	public static function & getObject(
		mixed &$stored, string $class_name, object $object = null,
		Reflection_Property|string $property = null
	) : ?object
	{
		if (self::$ignore || is_object($stored)) {
			return $stored;
		}
		if ($property instanceof Reflection_Property) {
			$property_name = $property->name;
		}
		elseif (is_string($property) && is_object($object)) {
			$property_name = $property;
			/** @noinspection PhpUnhandledExceptionInspection get_class and need valid property */
			$property = new Reflection_Property($object, $property_name);
		}
		if ($property && $property->getAnnotation('component')->value) {
			$foreign_property_name = Foreign_Annotation::of($property)->value;
			if ($foreign_property_name && Dao::getObjectIdentifier($object)) {
				$stored = Dao::searchOne(
					[$foreign_property_name => $object], $property->getType()->getElementTypeAsString()
				);
			}
		}
		elseif (is_object($object) && isset($property_name)) {
			$id_property_name = 'id_' . $property_name;
			if (isset($object->$id_property_name)) {
				$stored = $object->$id_property_name;
			}
			$id_property_name_class = $id_property_name . '_class';
			if (isset($object->$id_property_name_class) && (new Type($class_name))->isAbstractClass()) {
				$class_name = $object->$id_property_name_class;
			}
		}
		if (isset($stored) && !is_object($stored)) {
			if (
				isset($property)
				&& Store_Annotation::of($property)->value
				&& !Store_Annotation::of($property)->isFalse()
			) {
				if (Store_Annotation::of($property)->isGz()) {
					$inflated = gzinflate($stored);
					if ($inflated !== false) {
						$stored = $inflated;
					}
				}
				switch (Store_Annotation::of($property)->value) {
					case Store_Annotation::JSON:
						$stored = json_decode($stored, true);
						$stored = static::schemaDecode($stored, $property);
						break;
					case Store_Annotation::SERIALIZE:
						$stored = unserialize($stored);
						break;
					default:
						// TODO QUESTION $class_name and $property_class_name... Isn't it the same ?
						// TODO QUESTION Is Builder::className($property_class_name) missing ?
						$property_class_name = $property->getType()->asString();
						if (is_a($property_class_name, Stringable::class, true)) {
							$stored = call_user_func([$property_class_name, 'fromString'], $stored);
						}
						elseif (method_exists($property_class_name, '__unserialize')) {
							$stored = unserialize($stored);
						}
						break;
				}
			}
			else {
				$class_name = Builder::className($class_name);
				$stored     = isset($property)
					? Dao::get($property->getAnnotation('dao')->value)->read($stored, $class_name)
					: Dao::read($stored, $class_name);
			}
		}
		return $stored;
	}

	//-------------------------------------------------------------------------------- getStringArray
	/**
	 * Use it for var string[] without @values : add @getter Getter::getStringArray
	 * TODO create @link StringArray as a shortcut
	 *
	 * @param $stored string|string[]
	 * @return string[]
	 */
	public static function & getStringArray(array|string &$stored) : array
	{
		if (is_string($stored)) {
			$stored = trim($stored) ? explode(LF, str_replace(',', LF, $stored)) : [];
		}
		return $stored;
	}

	//---------------------------------------------------------------------------------------- ignore
	/**
	 * Changes the state of self::$ignore
	 *
	 * @example
	 * $getter_ignore = Getter::ignore(true);
	 * // .. do some stuff
	 * Getter::ignore($getter_ignore);
	 * @param $ignore boolean new state for self::$ignore
	 * @return boolean old state of self::$ignore
	 */
	public static function ignore(bool $ignore) : bool
	{
		$result       = self::$ignore;
		self::$ignore = $ignore;
		return $result;
	}

	//------------------------------------------------------------------------------------ invalidate
	/**
	 * Invalidate a property to force next read to call the getter again
	 *
	 * @param $object        object
	 * @param $property_name string
	 */
	public static function invalidate(object $object, string $property_name) : void
	{
		if (!isInitialized($object, $property_name)) {
			return;
		}
		$id_property_name = 'id_' . $property_name;
		if (isset($object->$id_property_name)) {
			$id = $object->$id_property_name;
		}
		unset($object->$property_name);
		if (isset($id)) {
			$object->$id_property_name = $id;
		}
	}

	//---------------------------------------------------------------------------------- schemaDecode
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $stored   array The object stored into an array : [$property_name => $value]
	 * @param $property Reflection_Property|null
	 * @return object
	 */
	public static function schemaDecode(array $stored, Reflection_Property $property = null) : object
	{
		$stored_array = $stored;
		if (isset($stored_array[Store_Annotation::JSON_CLASS])) {
			$class_name = $stored_array[Store_Annotation::JSON_CLASS];
			unset($stored_array[Store_Annotation::JSON_CLASS]);
		}
		elseif ($property?->getType()->isClass()) {
			$class_name = $property->getType()->getElementTypeAsString();
		}
		else {
			$class_name = '';
		}
		if ($class_name) {
			$class_name = Builder::className($class_name);
		}
		if (isset($stored_array[Store_Annotation::JSON_CONSTRUCT])) {
			$constructor_arguments = $stored_array[Store_Annotation::JSON_CONSTRUCT];
			if (!is_array($constructor_arguments)) {
				$constructor_arguments = [$constructor_arguments];
			}
			unset($stored_array[Store_Annotation::JSON_CONSTRUCT]);
		}
		else {
			$constructor_arguments = [];
		}
		if ($class_name) {
			/** @var $arrays_of_objects object[] object[$property_name][$key] */
			$arrays_of_objects = [];
			foreach ($stored_array as $property_name => $stored_value) {
				if (is_array($stored_value)) {
					/** @noinspection PhpUnhandledExceptionInspection stored data is valid */
					$property = new Reflection_Property($class_name, $property_name);
					$type     = $property->getType();
					if ($type->isClass() && $type->isMultiple() && !$type->isAbstractClass()) {
						$property_class_name = $type->getElementTypeAsString();
						foreach ($stored_value as $key => $object_identifier) {
							if (is_numeric($object_identifier)) {
								if (!isset($arrays_of_objects[$property_name])) {
									$arrays_of_objects[$property_name] = [];
								}
								$arrays_of_objects[$property_name][$key] = Dao::read(
									$object_identifier, $property_class_name
								);
							}
						}
					}
					else {
						$stored_array[$property_name] = Builder::fromSubarray($stored_value);
					}
				}
			}
			foreach ($arrays_of_objects as $property_name => $value) {
				unset($stored_array[$property_name]);
			}
			/** @noinspection PhpUnhandledExceptionInspection stored array is valid */
			$stored = Builder::fromArray($class_name, $stored_array, $constructor_arguments);
			foreach ($arrays_of_objects as $property_name => $value) {
				$stored->$property_name = $value;
			}
		}
		return $stored;
	}

}
