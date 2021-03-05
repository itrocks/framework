<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\Var_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Type;

/**
 * The same as PHP's ReflectionProperty, but working with PHP source, without loading the class
 */
class Reflection_Property implements Interfaces\Has_Doc_Comment, Interfaces\Reflection_Property
{
	use Annoted;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//------------------------------------------------------------------------------ $declaring_trait
	/**
	 * Cache for getDeclaringTrait() : please do never use it directly
	 *
	 * @var Reflection_Class
	 */
	private $declaring_trait;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * @var string
	 */
	private $doc_comment;

	//---------------------------------------------------------------------------------- $final_class
	/**
	 * @var Reflection_Class
	 */
	public $final_class;

	//------------------------------------------------------------------------------------ $is_static
	/**
	 * @var boolean
	 */
	private $is_static;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public $line;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------ $token_key
	/**
	 * The key for the T_VAR / T_PUBLIC / T_PROTECTED / T_PRIVATE token
	 *
	 * @var integer
	 */
	private $token_key;

	//----------------------------------------------------------------------------------- $visibility
	/**
	 * @values T_PUBLIC, T_PROTECTED, T_PRIVATE
	 * @var integer
	 */
	public $visibility;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class      Reflection_Class
	 * @param $name       string
	 * @param $line       integer
	 * @param $token_key  integer
	 * @param $visibility integer
	 */
	public function __construct(Reflection_Class $class, $name, $line, $token_key, $visibility)
	{
		$this->class       = $class;
		$this->final_class = $class;
		$this->line        = $line;
		$this->name        = $name;
		$this->token_key   = $token_key;
		$this->visibility  = $visibility;
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property.
	 * If the property has been declared into a trait, returns the class that uses this trait.
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringClass()
	{
		return $this->class;
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets the declaring class name for the reflected property.
	 * If the property has been declared into a trait, returns the name of the class using the trait.
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->class->name;
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
			$properties            = $this->getDeclaringClass()->getProperties([]);
			$this->declaring_trait = isset($properties[$this->name])
				? $this->getDeclaringClass()
				: $this->getDeclaringTraitInternal($this->getDeclaringClass());
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
				return $trait;
			}
		}
		foreach ($traits as $trait) {
			if ($used_trait = $this->getDeclaringTraitInternal($trait)) {
				return $used_trait;
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

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO use $flags ?
	 *
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment(array $flags = [])
	{
		if (!isset($this->doc_comment)) {
			$this->scanBefore();
		}
		return $this->doc_comment;
	}

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * Gets the final class where the property came from with a call to getProperties()
	 *
	 * @return Reflection_Class
	 */
	public function getFinalClass()
	{
		return $this->final_class;
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/**
	 * Gets final class name : the one where the property came from with a call to getProperties()
	 *
	 * @return string
	 */
	public function getFinalClassName()
	{
		return $this->final_class->name;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Reflection_Property
	 */
	public function getParent()
	{
		if (!isset($this->parent)) {
			$this->parent = false;
			$parent_class = $this->class->getParentClass();
			if ($parent_class) {
				$properties = $parent_class->getProperties([]);
				if (!isset($properties[$this->name])) {
					$properties = $parent_class->getProperties([T_USE]);
					if (!isset($properties[$this->name])) {
						$properties = $parent_class->getProperties([T_EXTENDS]);
					}
				}
				if (isset($properties[$this->name])) {
					$this->parent = $properties[$this->name];
				}
			}
		}
		return $this->parent ?: null;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type of the property, as defined by its var annotation
	 *
	 * @return Type
	 */
	public function getType()
	{
		return Var_Annotation::of($this)->getType();
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets value
	 *
	 * @param $object object
	 * @return mixed
	 */
	public function getValue($object)
	{
		return $object->{$this->name};
	}

	//------------------------------------------------------------------------------------- isPrivate
	/**
	 * @return boolean
	 */
	public function isPrivate()
	{
		return $this->visibility === T_PRIVATE;
	}

	//----------------------------------------------------------------------------------- isProtected
	/**
	 * @return boolean
	 */
	public function isProtected()
	{
		return $this->visibility === T_PROTECTED;
	}

	//-------------------------------------------------------------------------------------- isPublic
	/**
	 * @return boolean
	 */
	public function isPublic()
	{
		return $this->visibility === T_PUBLIC;
	}

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * Checks if property is static
	 *
	 * @return boolean
	 */
	public function isStatic()
	{
		if (!isset($this->is_static)) {
			$this->scanBefore();
		}
		return $this->is_static;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $class_name    string
	 * @param $property_name string
	 * @param $flags         integer[] @default [T_EXTENDS, T_USE] @values T_EXTENDS, T_USE
	 * @return Reflection_Property
	 */
	public static function of($class_name, $property_name, array $flags = null)
	{
		$properties = Reflection_Class::of($class_name)->getProperties($flags);
		return isset($properties[$property_name]) ? $properties[$property_name] : null;
	}

	//----------------------------------------------------------------------------------------- regex
	/**
	 * Gets the preg expression to find a property in PHP source
	 * If no property name is given, the preg expression to find all properties in source is returned
	 *
	 * Preg matching records will be :
	 * - 0 : the full property prototype
	 * - 1 : indent characters (including '\n')
	 * - 2 : the last phpdocumentor documentation before the function keyword
	 * - 3 : 'private', 'protected', 'public' or empty
	 * - 4 : 'static' or empty
	 * - 5 : the name of the property
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function regex($property_name = null)
	{
		$name = isset($property_name) ? $property_name : '\w+';
		return '%'
		. '(\n\s*?)'                                // 1 : indent
		. '(?:(/\*\*\n(?:\s*\*.*\n)*\s*\*/)\n\s*)?' // 2 : documentation
		. '(?:\/\*.*\*/\n\s*)?'                     // ignored one-line documentation
		. '(private|protected|public|var)\s+'       // 3 : visibility
		. '(?:(static)\s+)?'                        // 4 : static
		. '\$(' . $name . ')\s*'                    // 5 : name
		// . '(?:\=\s*((?:.*?\n?)*?)\s*)?'             // 6 : default : crashes with Macros;;$macros
		// . ';\s*\n'
		. '%';
	}

	//------------------------------------------------------------------------------------ scanBefore
	/**
	 * TODO doc
	 */
	private function scanBefore()
	{
		$this->doc_comment = '';
		$this->is_static   = false;
		$tokens            =& $this->class->source->getTokens();
		$token_key         = $this->token_key;
		while (is_array($token = $tokens[--$token_key])) {
			switch ($token[0]) {
				case T_DOC_COMMENT:
					$this->doc_comment = $token[1] . $this->doc_comment;
					break;
				case T_STATIC:
					$this->is_static = true;
					break;
			}
		}
	}

}
