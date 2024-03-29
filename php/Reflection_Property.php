<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Property\Var_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Attribute\Property_Has_Attributes;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Type;

/**
 * The same as PHP's ReflectionProperty, but working with PHP source, without loading the class
 */
class Reflection_Property implements Interfaces\Has_Doc_Comment, Interfaces\Reflection_Property
{
	use Annoted;
	use Common, Property_Has_Attributes {
		Common::getAttributesCommon insteadof Property_Has_Attributes;
	}

	//----------------------------------------------------------------------------------- $attributes
	/** @var Reflection_Attribute[] */
	private array $attributes;

	//---------------------------------------------------------------------------------------- $class
	public Reflection_Class $class;

	//------------------------------------------------------------------------------ $declaring_trait
	/**
	 * Cache for getDeclaringTrait() : please do never use it directly
	 */
	private Reflection_Class $declaring_trait;

	//---------------------------------------------------------------------------------- $doc_comment
	private string $doc_comment;

	//---------------------------------------------------------------------------------- $final_class
	public Reflection_Class $final_class;

	//------------------------------------------------------------------------------------ $is_static
	private bool $is_static;

	//----------------------------------------------------------------------------------------- $line
	public int $line;

	//----------------------------------------------------------------------------------------- $name
	public string $name;

	//--------------------------------------------------------------------------------------- $parent
	protected ?Interfaces\Reflection_Property $parent;

	//------------------------------------------------------------------------------------ $token_key
	/** The key for the T_VAR / T_PUBLIC / T_PROTECTED / T_PRIVATE token */
	private int $token_key;

	//----------------------------------------------------------------------------------------- $type
	public string $type = '';

	//----------------------------------------------------------------------------------- $visibility
	#[Values(T_PUBLIC, T_PROTECTED, T_PRIVATE)]
	public int $visibility;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class      Reflection_Class
	 * @param $name       string
	 * @param $line       integer
	 * @param $token_key  integer
	 * @param $visibility integer
	 * @param $attributes Reflection_Attribute[]
	 */
	public function __construct(
		Reflection_Class $class, string $name, int $line, int $token_key, int $visibility,
		array $attributes
	) {
		$this->attributes  = $attributes;
		$this->class       = $class;
		$this->final_class = $class;
		$this->line        = $line;
		$this->name        = $name;
		$this->token_key   = $token_key;
		$this->visibility  = $visibility;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->getFinalClassName() . '::$' . $this->getName();
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property.
	 * If the property has been declared into a trait, returns the class that uses this trait.
	 */
	public function getDeclaringClass() : Reflection_Class
	{
		return $this->class;
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets the declaring class name for the reflected property.
	 * If the property has been declared into a trait, returns the name of the class using the trait.
	 */
	public function getDeclaringClassName() : string
	{
		return $this->class->name;
	}

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * Gets the declaring trait for the reflected property
	 * If the property has been declared into a class, this returns this class
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
	 */
	public function getFinalClass() : Reflection_Class
	{
		return $this->final_class;
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/**
	 * Gets final class name : the one where the property came from with a call to getProperties()
	 */
	public function getFinalClassName() : string
	{
		return $this->final_class->name;
	}

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- getParent
	public function getParent() : ?Interfaces\Reflection_Property
	{
		if (isInitialized($this, 'parent')) {
			return $this->parent;
		}
		$this->parent = null;
		$parent_class = $this->class->getParentClass();
		if (!$parent_class) {
			return $this->parent;
		}
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
		return $this->parent;
	}

	//---------------------------------------------------------------------------------- getRootClass
	public function getRootClass() : Reflection_Class
	{
		return $this->getFinalClass();
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type of the property, as defined by its var annotation
	 */
	public function getType() : Type
	{
		return Var_Annotation::of($this)->getType();
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets value
	 */
	public function getValue(object $object) : mixed
	{
		return $object->{$this->name};
	}

	//-------------------------------------------------------------------------------------------- is
	public function is(Interfaces\Reflection_Property $object) : bool
	{
		return ($object->getName() === $this->getName())
			&& ($object->getDeclaringClassName() === $this->getDeclaringClassName());
	}

	//------------------------------------------------------------------------------------- isPrivate
	public function isPrivate() : bool
	{
		return $this->visibility === T_PRIVATE;
	}

	//----------------------------------------------------------------------------------- isProtected
	public function isProtected() : bool
	{
		return $this->visibility === T_PROTECTED;
	}

	//-------------------------------------------------------------------------------------- isPublic
	public function isPublic() : bool
	{
		return $this->visibility === T_PUBLIC;
	}

	//-------------------------------------------------------------------------------------- isStatic
	/**
	 * Checks if property is static
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
	private function scanBefore() : void
	{
		$square_brace_depth = 0;
		$this->doc_comment  = '';
		$this->is_static    = false;
		$tokens             =& $this->class->source->getTokens();
		$token_key          = $this->token_key;
		while (is_array($token = $tokens[--$token_key]) || ($token === ']')) {
			switch ($token[0]) {
				case T_DOC_COMMENT:
					if (!$square_brace_depth) {
						$this->doc_comment = $token[1] . $this->doc_comment;
					}
					break;
				case T_STATIC:
					$this->is_static = true;
					break;
				case ']':
					$square_brace_depth ++;
					break;
				case '[':
				case T_ATTRIBUTE:
					$square_brace_depth --;
			}
		}
	}

}
