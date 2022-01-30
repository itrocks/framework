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
	public Reflection_Class $class;

	//------------------------------------------------------------------------------ $declaring_trait
	/**
	 * Cache for getDeclaringTrait() : please do never use it directly
	 *
	 * @var Reflection_Class
	 */
	private Reflection_Class $declaring_trait;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * @var string
	 */
	private string $doc_comment;

	//---------------------------------------------------------------------------------- $final_class
	/**
	 * @var Reflection_Class
	 */
	public Reflection_Class $final_class;

	//------------------------------------------------------------------------------------ $is_static
	/**
	 * @var boolean
	 */
	private bool $is_static;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public int $line;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public string $name;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @var Reflection_Property|boolean|null
	 */
	protected Reflection_Property|bool|null $parent;

	//------------------------------------------------------------------------------------ $token_key
	/**
	 * The key for the T_VAR / T_PUBLIC / T_PROTECTED / T_PRIVATE token
	 *
	 * @var integer
	 */
	private int $token_key;

	//----------------------------------------------------------------------------------- $visibility
	/**
	 * @values T_PUBLIC, T_PROTECTED, T_PRIVATE
	 * @var integer
	 */
	public int $visibility;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class      Reflection_Class
	 * @param $name       string
	 * @param $line       integer
	 * @param $token_key  integer
	 * @param $visibility integer
	 */
	public function __construct(
		Reflection_Class $class, string $name, int $line, int $token_key, int $visibility
	) {
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
	public function getDeclaringClass() : Reflection_Class
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
	public function getDeclaringClassName() : string
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
	public function getDeclaringTrait() : Reflection_Class
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
	 * @return ?Reflection_Class
	 */
	private function getDeclaringTraitInternal(Reflection_Class $class) : ?Reflection_Class
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
	public function getDeclaringTraitName() : string
	{
		return $this->getDeclaringTrait()->getName();
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO use $flags ?
	 *
	 * @param $flags integer[]|null T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment(array|null $flags = []) : string
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
	public function getFinalClass() : Reflection_Class
	{
		return $this->final_class;
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/**
	 * Gets final class name : the one where the property came from with a call to getProperties()
	 *
	 * @return string
	 */
	public function getFinalClassName() : string
	{
		return $this->final_class->name;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return ?Reflection_Property
	 */
	public function getParent() : ?Reflection_Property
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
	public function getType() : Type
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
	public function getValue($object) : mixed
	{
		return $object->{$this->name};
	}

	//------------------------------------------------------------------------------------- isPrivate
	/**
	 * @return boolean
	 */
	public function isPrivate() : bool
	{
		return $this->visibility === T_PRIVATE;
	}

	//----------------------------------------------------------------------------------- isProtected
	/**
	 * @return boolean
	 */
	public function isProtected() : bool
	{
		return $this->visibility === T_PROTECTED;
	}

	//-------------------------------------------------------------------------------------- isPublic
	/**
	 * @return boolean
	 */
	public function isPublic() : bool
	{
		return $this->visibility === T_PUBLIC;
	}

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * Checks if property is static
	 *
	 * @return boolean
	 */
	public function isStatic() : bool
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
	 * @return ?Reflection_Property
	 */
	public static function of(string $class_name, string $property_name, array $flags = null)
		: ?Reflection_Property
	{
		$properties = Reflection_Class::of($class_name)->getProperties($flags);
		return $properties[$property_name] ?? null;
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
	public static function regex(string $property_name = '') : string
	{
		$name = $property_name ?: '\w+';
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
