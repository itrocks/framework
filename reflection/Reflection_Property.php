<?php
namespace ITRocks\Framework\Reflection;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Feature\Validate\Property\Mandatory_Annotation;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Mapper\Map;
use ITRocks\Framework\Property\Path;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Class_\Override_Annotation;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Var_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Var_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;
use ITRocks\Framework\Tools\Can_Be_Empty;
use ITRocks\Framework\Tools\Date_Interval;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Date_Time_Error;
use ITRocks\Framework\Tools\Field;
use ITRocks\Framework\Tools\Names;
use ReflectionException;
use ReflectionProperty;

/**
 * A rich extension of the PHP ReflectionProperty class
 */
class Reflection_Property extends ReflectionProperty
	implements Field, Has_Doc_Comment, Interfaces\Reflection_Property
{
	use Annoted;

	//----------------------------------------------------------------------------------- EMPTY_VALUE
	const EMPTY_VALUE = '~~EMPTY~VALUE~~';

	//---------------------------------------------------------------------------------------- $alias
	/**
	 * Aliased name
	 *
	 * @var string
	 */
	public $alias;

	//--------------------------------------------------------------------------------- $aliased_path
	/**
	 * Same as $path but all parts aliased
	 *
	 * @see $path
	 * @var string
	 */
	public $aliased_path;

	//------------------------------------------------------------------------------ $declaring_trait
	/**
	 * Cache for getDeclaringTrait() : please do never use it directly
	 *
	 * @var Reflection_Class
	 */
	private $declaring_trait;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private $doc_comment;

	//---------------------------------------------------------------------------------- $final_class
	/**
	 * Final class asked when calling getInstanceOf().
	 * It may not be the class where the property is declared, but the class which was asked.
	 *
	 * @var string
	 */
	public $final_class;

	//-------------------------------------------------------------------------- $overridden_property
	/**
	 * Only if the property is declared into a parent class as well as into the child class.
	 * If not, this will be false.
	 *
	 * @var Reflection_Property|boolean
	 */
	private $overridden_property;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * Full path of the property, if built with getInstanceOf() and a $property.path
	 *
	 * @var string
	 */
	public $path;

	//----------------------------------------------------------------------------------- $root_class
	/**
	 * This is the root class for the path if there is one
	 * This can be null if $this->path does not start from root class and must be ignored into
	 * getValue() and setValue()
	 *
	 * @var string
	 */
	public $root_class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    object|string
	 * @param $property_name string
	 * @throws ReflectionException
	 */
	public function __construct($class_name, $property_name)
	{
		if (is_object($class_name)) {
			$object     = $class_name;
			$class_name = get_class($class_name);
		}
		if (strpos($property_name, ')')) {
			$class_property = (new Path($class_name, $property_name))->toPropertyClassName();
			if (count($class_property) === 2) {
				list($class_name, $property_name) = $class_property;
			}
			// else : do nothing here, but it is not sure it this the right thing to do...
		}
		$this->path       = $property_name;
		$this->root_class = $class_name;
		$i                = 0;
		$aliases          = [];
		while (($j = strpos($property_name, DOT, $i)) !== false) {
			$property = new Reflection_Property($class_name, substr($property_name, $i, $j - $i));
			if (isset($object)) {
				$object = $object->{$property->name};
				if (is_object($object)) {
					$class_name = get_class($object);
				}
				elseif (is_array($object) && is_object(reset($object))) {
					$class_name = get_class(reset($object));
				}
				else {
					$class_name = $property->getType()->getElementTypeAsString();
				}
			}
			else {
				$class_name = $property->getType()->getElementTypeAsString();
			}
			$aliases[] = $property->alias;
			$i         = $j + 1;
		}
		if ($i) {
			$property_name = substr($property_name, $i);
		}
		$this->final_class = $class_name;
		parent::__construct($class_name, $property_name);
		$this->alias        = Alias_Annotation::of($this)->value;
		$this->aliased_path = $aliases ? implode(DOT, $aliases) . DOT . $this->alias : $this->alias;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the property
	 */
	public function __toString()
	{
		return $this->name;
	}

	//---------------------------------------------------------------------------------------- exists
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    string a class name
	 * @param $property_name string a property name or a property path starting from the class
	 * @return boolean true if the property exists
	 */
	public static function exists($class_name, $property_name)
	{
		if (strpos($property_name, ')')) {
			list($class_name, $property_name)
				= (new Path($class_name, $property_name))->toPropertyClassName();
		}
		if (strpos($property_name, DOT) !== false) {
			$properties_name = explode(DOT, $property_name);
			foreach (array_slice($properties_name, 0, -1) as $property_name) {
				if (!property_exists($class_name, $property_name)) {
					return false;
				}
				/** @noinspection PhpUnhandledExceptionInspection property_exists() was called */
				$property   = new Reflection_Property($class_name, $property_name);
				$class_name = $property->getType()->getElementTypeAsString();
			}
			$property_name = end($properties_name);
		}
		return property_exists($class_name, $property_name);
	}

	//---------------------------------------------------------------------------------------- filter
	/**
	 * Filter a list of properties : keep only those that match a class name
	 * You can reduce a list of properties using a parent class name using this function
	 *
	 * @param $properties Reflection_Property[]
	 * @param $class_name string
	 * @return Reflection_Property[]
	 * @throws ReflectionException
	 */
	public static function filter(array $properties, $class_name)
	{
		$class_properties = (new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]);
		foreach ($properties as $key => $property) {
			if (!isset($class_properties[$property->name])) {
				unset($properties[$key]);
			}
		}
		return $properties;
	}

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath()
	{
		return [$this->final_class, $this->name];
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getDeclaringClass()
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->class is always valid */
		return new Reflection_Class($this->class);
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets the declaring class name for the reflected property
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->class;
	}

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * Gets the declaring trait for the reflected property
	 * If the property has been declared into a class, this returns this class
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringTrait()
	{
		if (!isset($this->declaring_trait)) {
			$this->declaring_trait = $this->getDeclaringTraitInternal($this->getDeclaringClass())
				?: $this->getDeclaringClass();
		}
		return $this->declaring_trait;
	}

	//--------------------------------------------------------------------- getDeclaringTraitInternal
	/**
	 * @param $class Reflection_Class
	 * @return Reflection_Class
	 */
	private function getDeclaringTraitInternal(Reflection_Class $class)
	{
		$traits = $class->getTraits();
		foreach ($traits as $trait) {
			$properties = $trait->getProperties([]);
			if (isset($properties[$this->name])) {
				return $this->getDeclaringTraitInternal($trait) ?: $trait;
			}
		}
		return null;
	}

	//------------------------------------------------------------------------- getDeclaringTraitName
	/**
	 * Gets the declaring trait name for the reflected property
	 * If the property has been declared into a class, this returns this class name
	 *
	 * @return string
	 */
	public function getDeclaringTraitName()
	{
		return $this->getDeclaringTrait()->getName();
	}

	//------------------------------------------------------------------------------- getDefaultValue
	/**
	 * Gets the default value for the property
	 *
	 * This is not optimized and could be slower than getting the class's default values one time
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $use_annotation boolean|string Set this to false to disable interpretation of @default
	 *                        Set this to 'constant' to accept @default if @return_constant is set
	 * @param $default_object object INTERNAL, DO NOT USE ! An empty object for optimization purpose
	 * @return mixed
	 */
	public function getDefaultValue($use_annotation = true, &$default_object = null)
	{
		/** @var $default_annotation Method_Annotation */
		if (
			$use_annotation
			&& ($default_annotation = $this->getAnnotation('default'))->value
			&& (
				($use_annotation !== 'constant')
				|| $default_annotation->getReflectionMethod()->getAnnotation('return_constant')->value
			)
		) {
			if (!isset($default_object)) {
				/** @noinspection PhpUnhandledExceptionInspection final class name always valid */
				$default_object = Builder::create($this->getFinalClassName());
			}
			return $default_annotation->call($default_object);
		}
		return $this->getFinalClass()
			->getDefaultProperties([T_EXTENDS], $use_annotation, $this->name)[$this->name];
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO LOW use $flags ?
	 *
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @param $cache boolean true if save cache
	 * @return string
	 */
	public function getDocComment(array $flags = [T_USE], $cache = true)
	{
		if (!isset($this->doc_comment) || !$cache) {
			$overridden_property  = $this->getOverriddenProperty();
			$declaring_trait_name = $this->getDeclaringTrait()->name;
			$doc_comment          =
				$this->getOverrideDocComment()
				. LF . Parser::DOC_COMMENT_IN . $declaring_trait_name . LF
				. parent::getDocComment()
				. LF . Parser::DOC_COMMENT_IN . $declaring_trait_name . LF
				. ((isset($overridden_property)) ? $overridden_property->getDocComment() : '');
			if ($cache) {
				$this->doc_comment = $doc_comment;
			}
		}
		else {
			$doc_comment = $this->doc_comment;
		}
		if (strpos($this->path, DOT)) {
			$doc_comment = LF . Parser::DOC_COMMENT_IN . $this->root_class . LF
				. $this->getOverrideRootDocComment()
				. $doc_comment;
		}
		return $doc_comment;
	}

	//--------------------------------------------------------------------------------- getEmptyValue
	/**
	 * @return mixed
	 */
	public function getEmptyValue()
	{
		switch ($this->getType()->asString()) {
			case Type::_ARRAY:  return [];
			case Type::BOOLEAN: return false;
			case Type::FLOAT:   return .0;
			case Type::INTEGER: return 0;
			case Type::STRING:  return '';
		}
		return null;
	}

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * Gets the final class where the property came from with a call to getProperties()
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getFinalClass()
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->final_class is valid */
		return new Reflection_Class($this->final_class);
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/**
	 * Gets final class name : the one where the property came from with a call to getProperties()
	 *
	 * @return string
	 */
	public function getFinalClassName()
	{
		return $this->final_class;
	}

	//------------------------------------------------------------------------------ getFinalProperty
	/**
	 * Returns a new Reflection_Property object that matches the final property without property.path.
	 * If $this has no property.path (eg is already a final property) : returns $this.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Property
	 */
	public function getFinalProperty()
	{
		/** @noinspection PhpUnhandledExceptionInspection $this is valid */
		return strpos($this->path, DOT) ? new static($this->final_class, $this->name) : $this;
	}

	//------------------------------------------------------------------------- getOverriddenProperty
	/**
	 * Gets the parent property overridden by the current one from the parent class
	 *
	 * @return Reflection_Property
	 */
	public function getOverriddenProperty()
	{
		if (!isset($this->overridden_property)) {
			$parent                    = $this->getDeclaringClass()->getParentClass();
			$this->overridden_property = $parent ? ($parent->getProperty($this->name) ?: false) : false;
		}
		return $this->overridden_property ?: null;
	}

	//------------------------------------------------------------------------- getOverrideDocComment
	/**
	 * Gets the class override property doc comment that overrides the original property doc comment
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	private function getOverrideDocComment()
	{
		$comment = '';
		/** @noinspection PhpUnhandledExceptionInspection $this->final_class is always valid */
		foreach (
			(new Reflection_Class($this->final_class))->getListAnnotations('override') as $annotation
		) {
			/** @var $annotation Override_Annotation */
			if ($annotation->property_name === $this->name) {
				$comment .= '/**' . LF;
				foreach ($annotation->values() as $key => $value) {
					$comment .= Parser::DOC_COMMENT_IN . $annotation->class_name . LF
						. TAB . SP . '*' . SP . AT . $key . SP . $value . LF;
				}
				$comment .= TAB . SP . '*/';
			}
		}
		return $comment;
	}

	//--------------------------------------------------------------------- getOverrideRootDocComment
	/**
	 * In case of property.path : return override property.path values from the root class that
	 * match property.path
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	private function getOverrideRootDocComment()
	{
		$comment = '';
		/** @noinspection PhpUnhandledExceptionInspection $this->root_class is always valid */
		foreach (
			(new Reflection_Class($this->root_class))->getListAnnotations('override') as $annotation
		) {
			/** @var $annotation Override_Annotation */
			if ($annotation->property_name === $this->path) {
				$comment .= '/**' . LF;
				foreach ($annotation->values() as $key => $value) {
					$comment .= Parser::DOC_COMMENT_IN . $annotation->class_name
						. SP . '@override' . SP . $this->path . LF
						. TAB . SP . '*' . SP . AT . $key . SP . $value . LF;
				}
				$comment .= TAB . SP . '*/';
			}
		}
		return $comment;
	}

	//----------------------------------------------------------------------------- getParentProperty
	/**
	 * Gets the parent property for a $property.path
	 *
	 * @noinspection PhpDocMissingThrowsInspection $this->root_class is always valid
	 * @return Reflection_Property|null
	 */
	public function getParentProperty()
	{
		if (!empty($this->path) && ($i = strrpos($this->path, DOT))) {
			/** @noinspection PhpUnhandledExceptionInspection $this->root_class is always valid */
			return new Reflection_Property($this->root_class, substr($this->path, 0, $i));
		}
		return null;
	}

	//---------------------------------------------------------------------------------- getRootClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getRootClass()
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		return new Reflection_Class($this->root_class);
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type of the property, as defined by its var annotation
	 *
	 * @return Type
	 */
	public function getType()
	{
		$type = new Type(Var_Annotation::of($this)->value);
		if ($type->isNull()) {
			trigger_error(
				$this->class . '::$' . $this->name . ' type not set using @var annotation', E_USER_ERROR
			);
		}
		return $type;
	}

	//----------------------------------------------------------------------------------- getUserType
	/**
	 * @return Type
	 */
	public function getUserType()
	{
		$user_var_annotation_value = User_Var_Annotation::of($this)->value;
		return $user_var_annotation_value ? new Type($user_var_annotation_value) : $this->getType();
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets value
	 *
	 * @param $object       object
	 * @param $with_default boolean if true and property.path, will instantiate objects to get default
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function getValue($object = null, $with_default = false)
	{
		if (isset($this->root_class) && strpos($this->path, DOT)) {
			$class = $this->root_class;
			$path  = explode(DOT, $this->path);
			foreach ($path as $property_name) {
				/** @var $property Reflection_Property */
				if (isset($property)) {
					$type_name = $property->getType()->getElementTypeAsString();
					$class     = is_object($object) ? get_class($object) : Builder::className($type_name);
				}
				/** @noinspection PhpUnhandledExceptionInspection $class is valid */
				$property = new Reflection_Property($class, $property_name);
				if (is_array($object) || (is_a($object, $property->getFinalClassName(), true))) {
					$object = is_array($object)
						? $property->getValues($object, $with_default)
						: $property->getValue($object, $with_default);
				}
				if ($with_default && !$object && !is_array($object)) {
					$type = $property->getType();
					if ($type->isClass()) {
						$object = $property->getType()->asReflectionClass()->newInstance();
					}
				}
			}
			return $object;
		}
		// TODO Remove this patch, done because PHP 7.1 sometimes crash with no valid reason for this
		//return $object ? parent::getValue($object) : null;
		if ($object) {
			try {
				return parent::getValue($object);
			}
			catch (Exception $exception) {
				if (
					$exception->getMessage()
					=== 'Given object is not an instance of the class this property was declared in'
				) {
					$property_name = $this->name;
					return $object->$property_name;
				}
				/** @var $exception ReflectionException Only valid exception can be this (accessibility) */
				throw $exception;
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------------- getValues
	/**
	 * Get values from each object of an array
	 *
	 * @param $object       object[]
	 * @param $with_default boolean
	 * @return object[]
	 * @throws ReflectionException
	 */
	protected function getValues(array $object, $with_default)
	{
		// stored object
		$objects = $object;
		if (
			$this->getType()->isClass()
			&& Link_Annotation::of($this)->value
			&& !Store_Annotation::of($this)->value
		) {
			$sub_objects = new Map();
			foreach ($objects as $object) {
				$sub_objects->add($this->getValue($object, $with_default));
			}
			return $sub_objects->objects;
		}
		// final value
		$sub_objects = [];
		foreach ($objects as $key => $object) {
			$value           = $this->getValue($object, $with_default);
			$sub_objects[$value] = $value;
		}
		return $sub_objects;
	}

	//------------------------------------------------------------------------- isComponentObjectHtml
	/**
	 * An helper that returns information about the property containing a component, objects, etc.
	 *
	 * @return string @values component-object, component-objects, object, objects
	 */
	public function isComponentObjectHtml()
	{
		$type = $this->getType();

		if (!$type->isClass()) {
			return '';
		}

		$html = $type->isMultiple() ? 'objects' : 'object';
		if ($this->getAnnotation('component')->value || Link_Annotation::of($this)->isCollection()) {
			$html = 'component-' . $html;
		}
		return $html;
	}

	//---------------------------------------------------------------------------- isEquivalentObject
	/**
	 * Return true if the both objects match.
	 * If one is an object and the other is an integer, compare $objectX->id with $objectY
	 *
	 * @param $object1 object|integer
	 * @param $object2 object|integer
	 * @return boolean
	 * @throws Exception You compare a Date_Time with stuff that could not be converted to a Date_Time
	 */
	private function isEquivalentObject($object1, $object2)
	{
		if (is_object($object1) && isset($object1->id)) {
			$object1 = $object1->id;
		}
		if (is_object($object2) && isset($object2->id)) {
			$object2 = $object2->id;
		}
		// two Date_Time which differ of 1 hour or less are equivalent
		if (($object1 instanceof Date_Time) || ($object2 instanceof Date_Time)) {
			if ($object1 && !($object1 instanceof Date_Time)) {
				try {
					$object1 = new Date_Time($object1);
				}
				catch (Exception $exception) {
					$object1 = Date_Time_Error::fromError($object1);
				}
			}
			if ($object2 && !($object2 instanceof Date_Time)) {
				try {
					$object2 = new Date_Time($object2);
				}
				catch (Exception $exception) {
					$object2 = Date_Time_Error::fromError($object2);
				}
			}
		}
		if (
			($object1 instanceof Date_Time)
			&& ($object2 instanceof Date_Time)
			&& (Date_Interval::toHours($object1->diff($object2, true)) <= 1)
		) {
			return true;
		}
		return ($object1 == $object2);
	}

	//----------------------------------------------------------------------------------- isMandatory
	/**
	 * @return boolean|string
	 */
	public function isMandatory()
	{
		return Mandatory_Annotation::of($this)->value ? 'mandatory' : '';
	}

	//----------------------------------------------------------------------------------- isMultiline
	/**
	 * @return boolean|string
	 */
	public function isMultiline()
	{
		return $this->getAnnotation('multiline')->value ? 'multiline' : '';
	}

	//---------------------------------------------------------------------------------- isValueEmpty
	/**
	 * Returns true if property is empty
	 *
	 * @param $value mixed
	 * @return boolean
	 */
	public function isValueEmpty($value)
	{
		return (empty($value) && (is_object($value) || is_array($value) || (strval($value) !== '0')))
			|| (is_object($value) && Empty_Object::isEmpty($value))
			|| (
				is_string($value) && (substr($value, 0, 10) === '0000-00-00')
				&& $this->getType()->isDateTime()
			)
			|| (($value instanceof Can_Be_Empty) && $value->isEmpty());
	}

	//------------------------------------------------------------------------- isValueEmptyOrDefault
	/**
	 * Returns true if property is empty or equals to the default value
	 *
	 * Date_Time properties are null if '0000-00-00' or empty date
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $value mixed
	 * @return boolean
	 */
	public function isValueEmptyOrDefault($value)
	{
		/** @noinspection PhpUnhandledExceptionInspection same type : if one is Date_Time, the other too */
		return $this->isValueEmpty($value)
			|| $this->isEquivalentObject($value, $this->getDefaultValue());
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
	public function isVisible($hide_empty_test = true, $hidden_test = true, $invisible_test = true)
	{
		$user_annotation = $this->getListAnnotation(User_Annotation::ANNOTATION);
		return !$this->isStatic()
			&& (!$hidden_test     || !$user_annotation->has(User_Annotation::HIDDEN))
			&& (!$invisible_test  || !$user_annotation->has(User_Annotation::INVISIBLE))
			&& (!$hide_empty_test || !$user_annotation->has(User_Annotation::HIDE_EMPTY));
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
		$path = Names::propertyPathToField($this->path);
		if ($class_with_id && ($type = $this->getType()) && $type->isClass() && !$type->isDateTime()) {
			if (strpos($path, DOT)) {
				$path .= '[id]';
			}
			else {
				$path = 'id_' . $path;
			}
		}
		return $path;
	}

	//------------------------------------------------------------------------------- pathIfDifferent
	/**
	 * @return string
	 */
	public function pathIfDifferent()
	{
		return ($this->path === $this->name) ? null : $this->path;
	}

	//-------------------------------------------------------------------------------------- setValue
	/**
	 * Sets value
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object|mixed object or static property value
	 * @param $value  mixed
	 */
	public function setValue($object, $value = self::EMPTY_VALUE)
	{
		if (isset($this->root_class) && strpos($this->path, DOT)) {
			$path = explode(DOT, $this->path);
			/** @noinspection PhpUnhandledExceptionInspection $this->root_class and $path are valid */
			$property = new Reflection_Property($this->root_class, array_shift($path));
			foreach ($path as $property_name) {
				/** @noinspection PhpUnhandledExceptionInspection case is controlled and valid */
				$object = $property->getValue($object);
				/** @noinspection PhpUnhandledExceptionInspection $this->path is valid */
				$property = new Reflection_Property(
					$property->getType()->getElementTypeAsString(), $property_name
				);
			}
			if ($value === self::EMPTY_VALUE) {
				$property->setValue($object);
			}
			else {
				$property->setValue($object, $value);
			}
		}
		elseif ($value === self::EMPTY_VALUE) {
			parent::setValue($object);
		}
		else {
			parent::setValue($object, $value);
		}
	}

	//----------------------------------------------------------------------------------- showSeconds
	/**
	 * @return string
	 */
	public function showSeconds()
	{
		if (!$this->getType()->isDateTime()) {
			return '';
		}
		return $this->getAnnotation('show_seconds')->value ? 'show-seconds' : '';
	}

	//-------------------------------------------------------------------------------------- showTime
	/**
	 * @return string
	 */
	public function showTime()
	{
		if (!$this->getType()->isDateTime()) {
			return '';
		}
		$show_time = $this->getAnnotation('show_time')->value;
		return in_array($show_time, ['always', 'auto', true], true) ? 'show-time' : '';
	}

	//--------------------------------------------------------------------- toReflectionPropertyValue
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @param $user   boolean
	 * @return Reflection_Property_Value
	 */
	public function toReflectionPropertyValue($object, $user = false)
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->class and $this->root_class valid */
		return new Reflection_Property_Value(
			$this->root_class ?: $this->class, $this->path, $object, false, $user
		);
	}

}
