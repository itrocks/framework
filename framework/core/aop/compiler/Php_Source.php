<?php
namespace SAF\AOP;

use ReflectionClass;
use SAF\Framework\Namespaces;

/**
 * PHP source buffer and toolbox
 */
class Php_Source
{

	//--------------------------------------------------------------------------------------- $buffer
	/**
	 * @var string
	 */
	public $buffer;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//-------------------------------------------------------------------------------------- $extends
	private $extends;

	//----------------------------------------------------------------------------------- $implements
	private $implements;

	//-------------------------------------------------------------------------------------- $methods
	private $methods;

	//------------------------------------------------------------------------------------ $namespace
	private $namespace;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string class name or file name
	 * @param $buffer     string
	 */
	public function __construct($class_name, &$buffer = null)
	{
		if ((strpos($class_name, SL) !== false) && file_exists($class_name)) {
			if (isset($buffer)) {
				$this->buffer =& $buffer;
			}
			else {
				$this->buffer = str_replace(CR, '', file_get_contents($class_name));
			}
			$this->class_name = $this->getClassName();
		}
		else {
			$this->class_name = $class_name;
			if (isset($buffer)) {
				$this->buffer =& $buffer;
			}
			else {
				$this->buffer = str_replace(CR, '', file_get_contents(
					(new ReflectionClass($class_name))->getFileName()
				));
			}
		}
	}

	//--------------------------------------------------------------------------- allReflectionTraits
	/**
	 * Gets all traits directly used by the current class
	 * - Traits used by directly used traits are listed
	 * - Traits used by parent classes are not listed
	 *
	 * @return string[]
	 */
	public function allReflectionTraits()
	{
		$traits = [$this->class_name];
		$get_traits = $traits;
		while ($get_traits) {
			$get_traits = [];
			foreach ($get_traits as $trait_name) {
				if ($uses = class_uses($trait_name)) {
					$get_traits = array_merge($get_traits, $uses);
					$traits = array_merge($traits, $uses);
				}
			}
		}
		array_shift($traits);
		return $traits;
	}

	//------------------------------------------------------------------------------------ cleanupAop
	/**
	 * @return boolean
	 */
	public function cleanupAop()
	{
		$buffer =& $this->buffer;
		// remove all '\r'
		$buffer = trim(str_replace(CR, '', $buffer));
		// remove since the line containing '//#### AOP' until the end of the file
		$expr = '%\n\s*//#+\s+AOP.*%s';
		preg_match($expr, $buffer, $match1);
		$buffer = preg_replace($expr, '$1', $buffer) . ($match1 ? LF . LF . '}' . LF : LF);
		// replace '/* public */ private [static] function name_(' by 'public [static] function name('
		$expr = '%(?:\n\s*/\*\*\s+@noinspection\s+PhpUnusedPrivateMethodInspection(?:\s+\w*)+\*/)?'
			. '(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)((private|protected|public)\s*)?'
			. '(static\s*)?function(\s+\w*)\_[0-9]*\s*\(%';
		preg_match($expr, $buffer, $match2);
		$buffer = preg_replace($expr, '$1$2$3$6function$7(', $buffer);
		return $match1 || $match2;
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * Gets class name from source
	 *
	 * @return string
	 */
	public function getClassName()
	{
		preg_match('`\n\s*(?:abstract\s+)?(?:class|interface|trait)\s+(\w*)`', $this->buffer, $match);
		return $match ? $match[1] : null;
	}

	//---------------------------------------------------------------------------------- getDocComment
	/**
	 * Gets doc comment of an element (property or function name)
	 *
	 * @param $name string
	 * @return string
	 */
	public function getDocComment($name)
	{
		$property = '\n\s*(?:private|protected|public)\s+\$' . $name . '\s*;';
		$method = '\n\s*(?:(?:private|protected|public)\s+)?(?:static\s+)?function\s+' . $name . '\s*\(';
		$expr = '%(?>(\n\s*/\*\*.+?\n\s*\*/))(?:' . $property. '|' . $method . ')%s';
		preg_match($expr, $this->buffer, $match);
		if (!$match) {
			foreach ($this->allReflectionTraits() as $trait_name) {
				$trait_source = new Php_Source($trait_name);
				$trait_source->cleanupAop();
				$match = $trait_source->getDocComment($name);
				if ($match) {
					return $match;
				}
			}
			$parent = $this->getParent();
			if ($parent) $parent->cleanupAop();
			return $parent ? $parent->getDocComment($name) : '';
		}
		return $match ? $match[1] : '';
	}

	//---------------------------------------------------------------------------------- getPrototype
	/**
	 * Gets the prototype of an existing method from the class
	 *
	 * Detailed match contains match as described bellow
	 * 0 : '[DOC]\n\tabstract public static function methodName (...) {'
	 * 1 : '[DOC]'
	 * 2 : '\n\t'
	 * 3 : 'abstract'
	 * 4 : 'public'
	 * 5 : 'static'
	 * 6 : '($param, &$param2 = CONSTANT)'
	 * 'preg': $full_preg_expression
	 * 'parent': true (set only if prototype was taken from a parent class)
	 * 'prototype': the prototype string
	 *
	 * @param $method_name string
	 * @param $detailed    boolean if true, all matching elements and the preg are returned
	 * @return string the full prototype of the method, or null if not found
	 */
	public function getPrototype($method_name, $detailed = false)
	{
		$expr = '%'
			. '(\n\s*)'
			. '(?:(/\*\*.*\*/)\n\s*)?'
			. '(?:(abstract)\s+)?'
			. '(?:(private|protected|public)\s+)?'
			. '(?:(static)\s+)?'
			. 'function\s+'
			. '(?:' . $method_name . ')'
			. '\s*\((.*)\)'
			. '\s*[\{\;]'
			. '%sU';
		preg_match($expr, $this->buffer, $match);
		if (!$match) {
			foreach ($this->allReflectionTraits() as $trait_name) {
				$trait_source = new Php_Source($trait_name);
				$trait_source->cleanupAop();
				$match = $trait_source->getPrototype($method_name, $detailed);
				if ($match) {
					return $match;
				}
			}
			$parent = $this->getParent();
			if ($parent) $parent->cleanupAop();
			$match = $parent ? $parent->getPrototype($method_name, $detailed) : null;
			if ($detailed && $match) {
				$match['parent'] = true;
			}
			return $match;
		}
		if ($detailed && $match) {
			$match['preg'] = $expr;
			$match['prototype'] = $match[0];
			if (isset($match[1])) $match['doc'] = $match[1];
			$match['indent'] = $match[2];
			if (isset($match[3])) $match['abstract'] = true;
			if (isset($match[4])) $match['visibility'] = $match[3];
			if (isset($match[5])) $match['static'] = true;
			$match['parameters'] = $match[6];
		}
		return $match ? ($detailed ? $match : $match[0]) : null;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Php_Source
	 */
	public function getParent()
	{
		$parent_class_name = get_parent_class($this->class_name);
		return $parent_class_name
			? new Php_Source($parent_class_name)
			: null;
	}

	//------------------------------------------------------------------------------------ getExtends
	/**
	 * @return string
	 * @todo Missing namespace adding
	 */
	public function getExtends()
	{
		if (!isset($this->extends)) {
			$class_name = Namespaces::shortClassName($this->class_name);
			$expr = '%\n\s*(?:class|interface|trait)\s+(?:' . $class_name . ')\s+(?:extends)\s+(\w+)%s';
			preg_match($expr, $this->buffer, $match);
			$this->extends = $match ? $match[1] : '';
		}
		return $this->extends;
	}

	//------------------------------------------------------------------------------------ getExtends
	/**
	 * @return string[]
	 * @todo Missing namespace adding
	 */
	public function getImplements()
	{
		if (!isset($this->implements)) {
			$class_name = Namespaces::shortClassName($this->class_name);
			$expr = '%\n\s*(?:class|interface|trait)\s+(?:' . $class_name . ')\s+'
				. '(?:(?:extends)\s+\w+\s+)?(?:implements)\s+(?:(\w+)(?:\s*,\s*)?)+';
			preg_match_all($expr, $this->buffer, $match);
			$this->implements = $match ? $match[1] : '';
		}
		return $this->implements;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * @return Php_Method[]
	 */
	public function getMethods()
	{
		if (!isset($this->methods)) {
			$this->methods = [];
			preg_match_all(Php_Method::regex(), $this->buffer, $match);
			foreach (array_keys($match[0]) as $n) {
				$method = Php_Method::fromMatch($this->class_name, $match, $n);
				$this->methods[$method->name] = $method;
			}
		}
		return $this->methods;
	}

	//---------------------------------------------------------------------------------- getNameSpace
	/**
	 * @return string
	 */
	public function getNamespace()
	{
		if (!isset($this->namespace)) {
			$expr = '%\n\s*(?:namespace)\s+(\w+)\s*[;\{\n]%s';
			preg_match($expr, $this->buffer, $match);
			$this->namespace = $match ? $match[1] : '';
		}
		return $this->namespace;
	}

	//------------------------------------------------------------------- getPrototypeParametersNames
	/**
	 * @param $prototype string
	 * @return string[]
	 */
	public function getPrototypeParametersNames($prototype)
	{
		$expr = '%\$\w*%s';
		preg_match_all($expr, $prototype, $match);
		return $match[0];
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * Returns true if the class or trait is abstract, or if this is an interface
	 *
	 * @param $name string method name
	 * @return boolean
	 */
	public function isAbstract($name = null)
	{
		if (isset($name)) {
			$prototype = $this->getPrototype($name, true);
			return isset($prototype['abstract']);
		}
		else {
			preg_match('`\n\s*(?:(abstract)\s+)?(class|interface|trait)\s+\w*`', $this->buffer, $match);
			return $match && ($match[1] || ($match[2] == 'interface'));
		}
	}

}
