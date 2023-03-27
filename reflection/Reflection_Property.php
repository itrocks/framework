<?php
namespace ITRocks\Framework\Reflection;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Mapper\Map;
use ITRocks\Framework\Property\Path;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Class_\Override_Annotation;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Reflection\Annotation\Property\Default_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Mandatory_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Var_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Var_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property_Has_Attributes;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;
use ITRocks\Framework\Tools\Can_Be_Empty;
use ITRocks\Framework\Tools\Date_Interval;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Date_Time_Error;
use ITRocks\Framework\Tools\Field;
use ITRocks\Framework\Tools\Names;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionType;
use ReturnTypeWillChange;

/**
 * A rich extension of the PHP ReflectionProperty class
 */
class Reflection_Property extends ReflectionProperty
	implements Field, Has_Doc_Comment, Interfaces\Reflection_Property
{
	use Annoted;
	use Property_Has_Attributes;

	//---------------------------------------------------------------------------------------- $alias
	/**
	 * Aliased name
	 *
	 * @var string
	 */
	public string $alias;

	//--------------------------------------------------------------------------------- $aliased_path
	/**
	 * Same as $path but all parts aliased
	 *
	 * @see $path
	 * @var string
	 */
	public string $aliased_path;

	//------------------------------------------------------------------------------ $declaring_trait
	/**
	 * Cache for getDeclaringTrait() : please do never use it directly
	 *
	 * @var Reflection_Class
	 */
	private Reflection_Class $declaring_trait;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private string $doc_comment;

	//---------------------------------------------------------------------------------- $final_class
	/**
	 * Final class asked when calling getInstanceOf().
	 * It may not be the class where the property is declared, but the class which was asked.
	 *
	 * @var string
	 */
	public string $final_class;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * Only if the property is declared into a parent class as well as into the child class.
	 * If not, this will be false.
	 *
	 * @var ?Reflection_Property
	 */
	private ?Reflection_Property $parent;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * Full path of the property, if built with getInstanceOf() and a $property.path
	 *
	 * @var string
	 */
	public string $path;

	//----------------------------------------------------------------------------------- $root_class
	/**
	 * This is the root class for the path if there is one
	 * This can be null if $this->path does not start from root class and must be ignored into
	 * getValue() and setValue()
	 *
	 * @var string
	 */
	public string $root_class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    object|string
	 * @param $property_name string
	 * @throws ReflectionException
	 */
	public function __construct(object|string $class_name, string $property_name)
	{
		if (is_object($class_name)) {
			$object     = $class_name;
			$class_name = get_class($class_name);
		}
		if (str_contains($property_name, ')')) {
			$class_property = (new Path($class_name, $property_name))->toPropertyClassName();
			if (count($class_property) === 2) {
				[$class_name, $property_name] = $class_property;
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
		$this->alias        = Alias::of($this)->value;
		$this->aliased_path = $aliases ? implode(DOT, $aliases) . DOT . $this->alias : $this->alias;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the property
	 */
	public function __toString() : string
	{
		return $this->getFinalClassName() . '::$' . $this->name;
	}

	//---------------------------------------------------------------------------------------- exists
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name    string a class name
	 * @param $property_name string a property name or a property path starting from the class
	 * @return boolean true if the property exists
	 */
	public static function exists(string $class_name, string $property_name) : bool
	{
		if (str_contains($property_name, ')')) {
			[$class_name, $property_name]
				= (new Path($class_name, $property_name))->toPropertyClassName();
		}
		if (str_contains($property_name, DOT)) {
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
	public static function filter(array $properties, string $class_name) : array
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
	protected function getAnnotationCachePath() : array
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
	public function getDeclaringClass() : Reflection_Class
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
	public function getDeclaringClassName() : string
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
	public function getDeclaringTrait() : Reflection_Class
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
	 * @return ?Reflection_Class
	 */
	private function getDeclaringTraitInternal(Reflection_Class $class) : ?Reflection_Class
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
	public function getDeclaringTraitName() : string
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
	 * @param $use_annotation boolean|string Set this to false disables interpretation of @default
	 *                        Set this to 'constant' to accept @default if @return_constant is set
	 * @param $default_object object|null INTERNAL, DO NOT USE ! Empty object for optimization purpose
	 * @return mixed
	 */
	public function getDefaultValue(
		bool|string $use_annotation = true, object &$default_object = null
	) : mixed
	{
		/** @var $default_annotation Method_Annotation */
		if (
			$use_annotation
			&& ($default_annotation = Default_Annotation::of($this))->value
			&& (
				($use_annotation !== 'constant')
				|| $default_annotation->getReflectionMethod()->getAnnotation('return_constant')->value
			)
		) {
			if (!$default_object) {
				/** @noinspection PhpUnhandledExceptionInspection final class name always valid */
				$final_class    = new Reflection_Class($this->getFinalClassName());
				$default_object = ($final_class->isAbstract() || $final_class->isInterface() || $final_class->isTrait())
					? null
					/** @noinspection PhpUnhandledExceptionInspection class valid and can be instantiated */
					: $final_class->newInstance();
			}
			try {
				$method     = new ReflectionMethod($default_annotation->value);
				$parameters = $method->getParameters();
				$parameter1 = $parameters[0] ?? null;
				$parameter2 = $parameters[1] ?? null;
			}
			catch (ReflectionException) {
				$parameter1 = $parameter2 = null;
			}
			return in_array('property', [$parameter1?->name, $parameter2?->name], true)
				? $default_annotation->call($default_object, [$this])
				: $default_annotation->call($default_object);
		}
		return $this->getFinalClass()
			->getDefaultProperties([T_EXTENDS], $use_annotation, $this->name)[$this->name] ?? null;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO LOW use $flags ?
	 *
	 * @param $flags integer[]|null T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @param $cache boolean true if save cache
	 * @return string
	 */
	public function getDocComment(array|null $flags = [T_USE], bool $cache = true) : string
	{
		if (!isset($this->doc_comment) || !$cache) {
			$overridden_property  = $this->getParent();
			$declaring_trait_name = $this->getDeclaringTrait()->name;
			$doc_comment          =
				$this->getOverrideDocComment()
				. LF . Parser::DOC_COMMENT_IN . $declaring_trait_name . LF
				. parent::getDocComment()
				. LF . Parser::DOC_COMMENT_IN . $declaring_trait_name . LF
				. $overridden_property?->getDocComment();
			if ($cache) {
				$this->doc_comment = $doc_comment;
			}
		}
		else {
			$doc_comment = $this->doc_comment;
		}
		if (str_contains($this->path, DOT)) {
			$doc_comment = LF . Parser::DOC_COMMENT_IN . $this->root_class . LF
				. $this->getOverrideRootDocComment()
				. $doc_comment;
		}
		return $doc_comment;
	}

	//--------------------------------------------------------------------------------- getEmptyValue
	/**
	 * @return array|bool|float|int|string|null
	 */
	public function getEmptyValue() : array|bool|float|int|string|null
	{
		return match($this->getType()->asString()) {
			Type::_ARRAY  => [],
			Type::BOOLEAN, Type::FALSE, Type::TRUE => false,
			Type::FLOAT   => .0,
			Type::INTEGER => 0,
			Type::STRING  => '',
			default       => null
		};
	}

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * Gets the final class where the property came from with a call to getProperties()
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getFinalClass() : Reflection_Class
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
	public function getFinalClassName() : string
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
	public function getFinalProperty() : Reflection_Property
	{
		/** @noinspection PhpUnhandledExceptionInspection $this is valid */
		return str_contains($this->path, DOT) ? new static($this->final_class, $this->name) : $this;
	}

	//------------------------------------------------------------------------- getOverrideDocComment
	/**
	 * Gets the class override property doc comment that overrides the original property doc comment
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	private function getOverrideDocComment() : string
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
	private function getOverrideRootDocComment() : string
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

	//------------------------------------------------------------------------------------- getParent
	/**
	 * Gets the parent property overridden by the current one from the parent class
	 *
	 * @return ?Interfaces\Reflection_Property
	 */
	public function getParent() : ?Interfaces\Reflection_Property
	{
		if (isInitialized($this, 'overridden_property')) {
			return $this->parent;
		}
		$parent_class = $this->getDeclaringClass()->getParentClass();
		try {
			$this->parent = $parent_class?->getProperty($this->name);
		}
		catch (ReflectionException) {
			$this->parent = null;
		}
		return $this->parent;
	}

	//----------------------------------------------------------------------------- getParentProperty
	/**
	 * Gets the parent property for a $property.path
	 *
	 * @noinspection PhpDocMissingThrowsInspection $this->root_class is always valid
	 * @return ?Reflection_Property
	 */
	public function getParentProperty() : ?Reflection_Property
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
	public function getRootClass() : Reflection_Class
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
	#[ReturnTypeWillChange]
	public function getType() : Type
	{
		$type = Var_Annotation::of($this)->getType();
		if ($type->isNull()) {
			trigger_error(
				$this->getDeclaringTrait() . '::$' . $this->name
				. ' type not set using @var annotation', E_USER_ERROR
			);
		}
		return $type;
	}

	//--------------------------------------------------------------------------------- getTypeOrigin
	public function getTypeOrigin() : ?ReflectionType
	{
		return parent::getType();
	}

	//----------------------------------------------------------------------------------- getUserType
	/**
	 * @return Type
	 */
	public function getUserType() : Type
	{
		$user_var_annotation_value = User_Var_Annotation::of($this)->value;
		return $user_var_annotation_value ? new Type($user_var_annotation_value) : $this->getType();
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
	public function getValue(object $object = null, bool $with_default = false) : mixed
	{
		if (isset($this->root_class) && str_contains($this->path, DOT)) {
			$class = $this->root_class;
			$path  = explode(DOT, $this->path);
			/** @var $property Reflection_Property */
			foreach ($path as $property_name) {
				$found_object = false;
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
					$found_object = true;
				}
				if ($with_default && !$object && !is_array($object)) {
					$type = $property->getType();
					if ($type->isClass()) {
						$object = $property->getType()->asReflectionClass()->newInstance();
						$found_object = true;
					}
				}
				if (!$found_object) {
					return null;
				}
			}
			return $object;
		}
		return $object ? parent::getValue($object) : null;
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
	protected function getValues(array $object, bool $with_default) : array
	{
		// stored object
		$objects = $object;
		if (
			$this->getType()->isClass()
			&& Link_Annotation::of($this)->value
			&& !Store::of($this)->isString()
		) {
			$sub_objects = new Map();
			foreach ($objects as $object) {
				$sub_objects->add($this->getValue($object, $with_default));
			}
			return $sub_objects->objects;
		}
		// final value
		$sub_objects = [];
		foreach ($objects as $object) {
			$value           = $this->getValue($object, $with_default);
			$sub_objects[$value] = $value;
		}
		return $sub_objects;
	}

	//-------------------------------------------------------------------------------------------- is
	public function is(Interfaces\Reflection_Property $object) : bool
	{
		return ($object->getName() === $this->getName())
			&& ($object->getDeclaringClassName() === $this->getDeclaringClassName());
	}

	//----------------------------------------------------------------------------------- isComponent
	/**
	 * @return boolean
	 */
	public function isComponent() : bool
	{
		return $this->getType()->isClass()
			&& (Component::of($this)?->value || Link_Annotation::of($this)->isCollection());
	}

	//------------------------------------------------------------------------- isComponentObjectHtml
	/**
	 * A helper that returns information about the property containing a component, objects, etc.
	 *
	 * @return string @values component-object, component-objects, object, objects
	 */
	public function isComponentObjectHtml() : string
	{
		$type = $this->getType();
		if (!$type->isClass()) {
			return '';
		}
		$html = $type->isMultiple() ? 'objects' : 'object';
		if ($this->isComponent()) {
			$html = 'component-' . $html;
		}
		return $html;
	}

	//---------------------------------------------------------------------------- isEquivalentObject
	/**
	 * Return true if the both objects match.
	 * If one is an object and the other is a string identifier, compare $objectX->id with $objectY
	 *
	 * @param $object1 object|string|null
	 * @param $object2 object|string|null
	 * @return boolean
	 * @throws Exception You compare a Date_Time with stuff that could not be converted to a Date_Time
	 */
	private function isEquivalentObject(mixed $object1, mixed $object2) : bool
	{
		if (is_object($object1) && isset($object1->id)) {
			$object1 = strval($object1->id);
		}
		if (is_object($object2) && isset($object2->id)) {
			$object2 = strval($object2->id);
		}
		// two Date_Time which differ of 1 hour or less are equivalent
		if (($object1 instanceof Date_Time) || ($object2 instanceof Date_Time)) {
			if ($object1 && !($object1 instanceof Date_Time)) {
				try {
					$object1 = new Date_Time($object1);
				}
				catch (Exception) {
					$object1 = Date_Time_Error::fromError($object1);
				}
			}
			if ($object2 && !($object2 instanceof Date_Time)) {
				try {
					$object2 = new Date_Time($object2);
				}
				catch (Exception) {
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
		return ($object1 === $object2);
	}

	//----------------------------------------------------------------------------------- isMandatory
	/**
	 * @return string
	 */
	public function isMandatory() : string
	{
		return Mandatory_Annotation::of($this)->value ? 'mandatory' : '';
	}

	//----------------------------------------------------------------------------------- isMultiline
	/**
	 * @return string
	 */
	public function isMultiline() : string
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
	public function isValueEmpty(mixed $value) : bool
	{
		return (
			(
				empty($value)
				&& (is_object($value) || is_array($value) || !in_array(strval($value), ['0', '-0'], true))
			)
			|| (is_object($value) && Empty_Object::isEmpty($value))
			|| (
				is_string($value) && str_starts_with($value, '0000-00-00')
				&& $this->getType()->isDateTime()
			)
			|| (($value instanceof Can_Be_Empty) && $value->isEmpty())
		);
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
	public function isValueEmptyOrDefault(mixed $value) : bool
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
	 * @param $hidden_test     boolean If false, will be visible event if @user hidden is set
	 * @param $invisible_test  boolean If false, will be visible event if @user invisible is set
	 * @return boolean
	 */
	public function isVisible(
		bool $hide_empty_test = true, bool $hidden_test = true, bool $invisible_test = true
	) : bool
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
	public function pathAsField(bool $class_with_id = false) : string
	{
		$path = Names::propertyPathToField($this->path);
		if ($class_with_id && ($type = $this->getType()) && $type->isClass() && !$type->isDateTime()) {
			if (str_contains($path, DOT)) {
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
	public function pathIfDifferent() : string
	{
		return ($this->path === $this->name) ? '' : $this->path;
	}

	//-------------------------------------------------------------------------------------- setValue
	/**
	 * Sets value
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection $object do not use for static
	 * @param $object object|mixed object or static property value
	 * @param $value  mixed
	 */
	public function setValue(mixed $object, mixed $value = null) : void
	{
		if (isset($this->root_class) && str_contains($this->path, DOT)) {
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
			$property->setValue($object, $value);
		}
		else {
			parent::setValue($object, $value);
		}
	}

	//----------------------------------------------------------------------------------- showSeconds
	/**
	 * @return string
	 */
	public function showSeconds() : string
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
	public function showTime() : string
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
	public function toReflectionPropertyValue(object $object, bool $user = false)
		: Reflection_Property_Value
	{
		/** @noinspection PhpUnhandledExceptionInspection $this->class and $this->root_class valid */
		return new Reflection_Property_Value(
			$this->root_class ?: $this->class, $this->path, $object, false, $user
		);
	}

}
