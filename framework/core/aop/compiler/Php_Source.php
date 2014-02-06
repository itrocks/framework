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

	//------------------------------------------------------------------------------------ $namespace
	private $namespace;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $buffer     string
	 */
	public function __construct($class_name, &$buffer = null)
	{
		$this->class_name = $class_name;
		if (isset($buffer)) {
			$this->buffer =& $buffer;
		}
		else {
			$this->buffer = file_get_contents((new ReflectionClass($class_name))->getFileName());
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
		$traits = array($this->class_name);
		$get_traits = $traits;
		while ($get_traits) {
			$get_traits = array();
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
		// remove all "\r"
		$buffer = trim(str_replace("\r", '', $buffer));
		// remove since the line containing "//#### AOP" until the end of the file
		$expr = '%\n\s*//#+\s+AOP.*%s';
		preg_match($expr, $buffer, $match1);
		$buffer = preg_replace($expr, '$1', $buffer) . ($match1 ? "\n\n}\n" : "\n");
		// replace "/* public */ private [static] function name_(" by "public [static] function name("
		$expr = '%(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)((private|protected|public)\s*)?'
			. '(static\s*)?function(\s+\w*)\_[0-9]*\s*\(%';
		preg_match($expr, $buffer, $match2);
		$buffer = preg_replace($expr, '$1$2$3$6function$7(', $buffer);
		return $match1 || $match2;
	}

	//---------------------------------------------------------------------------------- getDocComment
	/**
	 * Gets doc comment of an element (property or function name)
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
	 * 0 : "\n\tpublic static function methodName (...) {"
	 * 1 : "\n\t"
	 * 2 : "public "
	 * 3 : "public"
	 * 4 : "static function "
	 * 5 : "static "
	 * 6 : "methodName"
	 * 7 : " ($param, &$param2 = CONSTANT) {"
	 * 'preg': $full_preg_expression
	 * 'parent': true (set only if prototype was taken from a parent class)
	 * 'prototype': the prototype string
	 *
	 * @example "methodName" => "\n\tpublic static function methodName (...) {"
	 *
	 * @param $method_name string
	 * @param $detailed    boolean if true, all matching elements and the preg are returned
	 * @return string the full prototype of the method, or null if not found
	 */
	public function getPrototype($method_name, $detailed = false)
	{
		$expr = '`(\n\s*)((private|protected|public)\s*)?((static\s*)?function\s+)'
			. '(' . $method_name . ')(\s*\([^\{]*\)[\n*\s*]*\{)`';
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

}
