<?php
namespace SAF\AOP;

use ReflectionMethod;
use SAF\Framework\Reflection_Method;

/**
 * Object representation of a Php method read from source
 */
class Php_Method
{

	//------------------------------------------------------------------------------------- $abstract
	/**
	 * @var string 'abstract' or null
	 */
	public $abstract;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Php_Class
	 */
	public $class;

	//-------------------------------------------------------------------------------- $documentation
	/**
	 * @var string
	 */
	public $documentation;

	//--------------------------------------------------------------------------------------- $indent
	/**
	 * @var string
	 */
	public $indent;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------- $parameters_names
	/**
	 * @var string[]
	 */
	private $parameters_names;

	//---------------------------------------------------------------------------- $parameters_string
	/**
	 * @example '$p1, $p2 = null'
	 * @var string
	 */
	public $parameters_string;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @var Php_Method
	 */
	private $parent;

	//------------------------------------------------------------------------------------ $prototype
	/**
	 * @var string full method prototype (not changed if you alter another value)
	 */
	public $prototype;

	//--------------------------------------------------------------------------------------- $static
	/**
	 * @var string 'static' or null
	 */
	public $static;

	//----------------------------------------------------------------------------------- $visibility
	/**
	 * @var string 'private', 'protected', 'public' or null (implicitly public)
	 */
	public $visibility;

	//------------------------------------------------------------------------------------- fromMatch
	/**
	 * @param $class Php_Class
	 * @param $match array
	 * @param $n     integer
	 * @return Php_Method
	 */
	public static function fromMatch(Php_Class $class, $match, $n = null)
	{
		$method = new Php_Method();
		$method->class = $class;
		if (isset($n)) {
			$method->prototype         = $match[0][$n];
			$method->indent            = $match[1][$n];
			$method->documentation     = empty($match[2][$n]) ? null : $match[2][$n];
			$method->abstract          = empty($match[3][$n]) ? null : $match[3][$n];
			$method->visibility        = empty($match[4][$n]) ? null : $match[4][$n];
			$method->static            = empty($match[5][$n]) ? null : $match[5][$n];
			$method->name              = $match[6][$n];
			$method->parameters_string = $match[7][$n];
		}
		else {
			$method->prototype         = $match[0];
			$method->indent            = $match[1];
			$method->documentation     = empty($match[2]) ? null : $match[2];
			$method->abstract          = empty($match[3]) ? null : $match[3];
			$method->visibility        = empty($match[4]) ? null : $match[4];
			$method->static            = empty($match[5]) ? null : $match[5];
			$method->name              = $match[6];
			$method->parameters_string = $match[7];
		}
		return $method;
	}

	//-------------------------------------------------------------------------------- fromReflection
	/**
	 * @param $class      Php_Class
	 * @param $reflection ReflectionMethod
	 * @return Php_Method
	 */
	public static function fromReflection(Php_Class $class, ReflectionMethod $reflection)
	{
		$method = new Php_Method();
		$method->abstract = $reflection->isAbstract() ? 'abstract' : null;
		$method->class = $class;
		$method->documentation = $reflection->getDocComment();
		$method->name = $reflection->name;
		$method->parameters_string = join(
			', ', (new Reflection_Method($reflection->class, $reflection->name))->getParameters()
		);
		$method->parent = false;
		$method->static = $reflection->isStatic();
		$method->visibility = $reflection->isPrivate() ? 'private' : (
			$reflection->isProtected() ? 'protected' : 'public'
		);
		return $method;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Php_Method
	 */
	public function getParent()
	{
		if (!isset($this->parent)) {
			$this->parent = false;
			$class_parent = $this->class->getParent();
			if ($class_parent) {
				$methods = $class_parent->getMethods();
				if (!isset($methods[$this->name])) {
					$methods = $class_parent->getMethods(array('traits'));
					if (!isset($methods[$this->name])) {
						$methods = $class_parent->getMethods(array('inherited'));
					}
				}
				if (isset($methods[$this->name])) {
					$this->parent = $methods[$this->name];
				}
			}
		}
		return $this->parent ?: null;
	}

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @return string[]
	 */
	public function getParametersNames()
	{
		if (!isset($this->parameters_names)) {
			$expr = '%\$(\w+)%';
			preg_match_all($expr, $this->parameters_string, $match);
			$this->parameters_names = array_combine($match[1], $match[1]);
		}
		return $this->parameters_names;
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract()
	{
		return $this->abstract || ($this->class->type == 'interface');
	}

	//----------------------------------------------------------------------------------------- regex
	/**
	 * @param $method_name string
	 * @return string
	 */
	public static function regex($method_name = null)
	{
		$name = isset($method_name) ? $method_name : '\w+';
		return '%'
		. '(\n\s*?)'                                // 1 : indent
		. '(?:(/\*\*\n(?:\s*\*.*\n)*\s*\*/)\n\s*)?' // 2 : documentation
		. '(?:\/\*.*\*/\n\s*)?'                     // ignored one-line documentation
		. '(?:(abstract)\s+)?'                      // 3 : abstract
		. '(?:(private|protected|public)\s+)?'      // 4 : visibility
		. '(?:(static)\s+)?'                        // 5 : static
		. 'function\s+'                             // function keyword
		. '(' . $name . ')\s*'                      // 6 : name
		. '(\((?:.*?\n?)*?\)\s*)'                   // 7 : parameters string
		. '([\{\;]\s*?\n)'                          // 8 : end of function prototype
		. '%';
	}

}
