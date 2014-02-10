<?php
namespace SAF\AOP;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use SAF\Framework\Application;
use SAF\Framework\Getter;
use SAF\Framework\Names;
use SAF\Plugins;

/**
 * Standard aspect weaver compiler
 */
class Compiler implements ICompiler
{

	const DEBUG = false;

	//----------------------------------------------------------------------------- $compiled_classes
	/**
	 * @var boolean[] key is class name, value is always true
	 */
	private $compiled_classes = array();

	//--------------------------------------------------------------------------------------- $weaver
	/**
	 * @var Weaver
	 */
	private $weaver;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $weaver IWeaver
	 */
	public function __construct(IWeaver $weaver)
	{
		$this->weaver = $weaver;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * Compile aspects for all weaved pointcuts
	 */
	public function compile()
	{
		$start_time = microtime(true);
		if (!($this->weaver instanceof Weaver)) {
			trigger_error('Compiler can only compile aspect weaver of class Weaver', E_USER_ERROR);
			return;
		}
		foreach ($this->weaver->getJoinpoints() as $joinpoint => $pointcuts) {
			if (ctype_lower($joinpoint)) {
				$this->willCompileFunction($joinpoint, $pointcuts);
			}
			elseif (!isset($this->compiled_classes[$joinpoint])) {
				$this->compileClass($joinpoint);
			}
		}
		if (self::DEBUG) echo 'duration = ' . (microtime(true) - $start_time) . '<br>';
	}

	//------------------------------------------------------------------------------------ compileAll
	/**
	 * @param $weaver IWeaver
	 */
	public function compileAll(IWeaver $weaver)
	{
		foreach (Application::current()->include_path->getSourceFiles() as $file_name) {
			if (substr($file_name, -4) == '.php') {
				$buffer = str_replace("\r", '', file_get_contents($file_name));
				preg_match(
					'`\n\s*(abstract\s+)?(class|trait)\s+([^\s]*)`', $buffer, $match
				);
				if ($match) {
					$class_name = $match[3];
					preg_match('`\n\s*namespace\s*([^;\s\{]*)`', $buffer, $match);
					if ($match) {
						$class_name = $match[1] . '\\' . $class_name;
					}
					if (!isset($this->compiled_classes[$class_name])) {
						$this->compileClass($class_name, $file_name);
					}
					if (self::DEBUG) echo '- compile class ' . $class_name . '<br>';
				}
				else {
					if (self::DEBUG) echo '<b>- nothing into ' . $file_name . '</b><br>';
				}
			}
		}
		$this->compile($weaver);
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * @param $class_name string the name of the class or trait to be compiled
	 * @param $file_name  string file name (optional)
	 */
	private function compileClass($class_name, $file_name = null)
	{
		if (isset($file_name)) {
			/** @noinspection PhpIncludeInspection */
			include_once $file_name;
			$class = new ReflectionClass($class_name);
		}
		else {
			$class = new ReflectionClass($class_name);
			$file_name = $class->getFileName();
		}
		$buffer = file_get_contents($file_name);
		$cleanup = (new Php_Source($class_name, $buffer))->cleanupAop();
		if (self::DEBUG) echo 'cleanup of $class_name = ' . $cleanup . '<br>';

		if (isset($_GET['C'])) {
			echo 'CLEANUP-ONLY ' . $class_name . '<br>';
			$methods = $properties = array();
		}
		else {

			if (self::DEBUG) echo '<h2>compile class ' . $class_name. '</h2>';
			$buffer = substr($buffer, 0, -2) . "\t//" . str_repeat('#', 91) . " AOP\n";

			$methods    = array();
			$properties = array();
			if (!$class->isInterface()) {
				if (!$class->isTrait()) {
					$this->scanForLinks($properties,   $class);
					$this->scanForGetters($properties, $class);
					$this->scanForSetters($properties, $class);
				}
				$this->scanForMethods($methods, $class);
			}

			list($methods2, $properties2) = $this->getPointcuts($class_name);
			$methods    = arrayMergeRecursive($methods, $methods2);
			$properties = arrayMergeRecursive($properties, $properties2);

			if ($properties) {
				$properties_compiler = new Properties_Compiler($class_name, $buffer);
				foreach ($properties as $property_name => $advices) {
					$properties_compiler->compileProperty($property_name, $advices);
				}
				$methods_code = $properties_compiler->getCompiledMethods();
			}
			else {
				$methods_code = array();
			}

			$method_compiler = new Method_Compiler($class_name, $buffer);
			foreach ($methods as $method_name => $advices) {
				$methods_code[$method_name] = $method_compiler->compile($method_name, $advices);
			}

			ksort($methods_code);
			$buffer .= join('', $methods_code) . "\n}\n";

		}

		if ($cleanup || $methods || $properties) {
			if (isset($_GET['D'])) echo '<pre>' . htmlentities($buffer) . '</pre>';
			if (isset($_GET['R'])) echo 'READ-ONLY ' . $class_name . '<br>';
			else file_put_contents($file_name, $buffer);
			if (self::DEBUG) echo '<pre>' . htmlentities($buffer) . '</pre>';
		}

		$this->compiled_classes[$class_name] = true;
	}

	//---------------------------------------------------------------------------------- getPointcuts
	/**
	 * @param $class_name string
	 * @return array[] two elements : array($methods, $properties)
	 */
	private function getPointcuts($class_name)
	{
		$methods    = array();
		$properties = array();
		foreach ($this->weaver->getJoinpoints($class_name) as $joinpoint2 => $pointcuts2) {
			foreach ($pointcuts2 as $pointcut) {
				if (($pointcut[0] == 'read') || ($pointcut[0] == 'write')) {
					$properties[$joinpoint2] = $pointcuts2;
				}
				else {
					$methods[$joinpoint2] = $pointcuts2;
				}
			}
		}
		return array($methods, $properties);
	}

	//----------------------------------------------------------------------------- isPropertyInClass
	/**
	 * Returns true if the property is declared into the class, or into traits used by the class
	 * or by a trait used by the class
	 *
	 * @param $property ReflectionMethod|ReflectionProperty
	 * @param $class    ReflectionClass
	 * @return boolean
	 */
	private function isInClass($property, ReflectionClass $class)
	{
		if ($property->class == $class->name) return true;
		$traits = array($class->name);
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
		if (self::DEBUG) echo '&gt; Traits for ' . $class->name . ' are ' . print_r($traits, true) . '<br>';
		return in_array($property->class, $traits);
	}

	//-------------------------------------------------------------------------------- scanForMethods
	/**
	 * @param $methods array
	 * @param $class   ReflectionClass
	 */
	private function scanForMethods(&$methods, ReflectionClass $class)
	{
		foreach ($class->getMethods() as $method) {
			if (!$method->isAbstract() && ($method->class == $class->name)) {
				$doc_comment = $method->getDocComment();
				$expr = '%\n\s+\*\s+@(after|around|before|)\s+(?:(\w+)::)?(\w+)(\($this\))?%';
				preg_match_all($expr, $doc_comment, $match);
				if ($match) {
					foreach (array_keys($match[0]) as $key) {
						$type        = $match[0][$key];
						$class_name  = $match[1][$key] ?: '$this';
						$method_name = $match[2][$key];
						$has_this    = $match[3][$key];
						$aspect = array($type, array($method->class, $method->name));
						if ($has_this) {
							$aspect[] = $has_this;
						}
						$methods[$class_name][$method_name] = $aspect;
					}
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- scanForGetters
	/**
	 * @param $properties array
	 * @param $class      ReflectionClass
	 */
	private function scanForGetters(&$properties, ReflectionClass $class)
	{
		foreach ($class->getProperties() as $property) {
			if ($this->isInClass($property, $class)) {
				$doc_comment = $property->getDocComment();
				preg_match('%\n\s+\*\s+@getter(?:\s+(?:(\w+)::)?(\w+)?)?%', $doc_comment, $match);
				if ($match) {
					$advice = array(
						empty($match[1]) ? '$this' : $match[1],
						isset($match[2]) ? $match[2] : Names::propertyToMethod($property->name, 'get')
					);
					$properties[$property->name][] = array('read', $advice);
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- scanForLinks
	/**
	 * @param $properties array
	 * @param $class      ReflectionClass
	 */
	private function scanForLinks(&$properties, ReflectionClass $class)
	{
		foreach ($class->getProperties() as $property) {
			if (($property->class == $class->name)) {
				$doc_comment = $property->getDocComment();
				if (strpos($doc_comment, '* @link')) {
					$expr = '%\n\s+\*\s+@link\s+(All|Collection|DateTime|Map|Object)%';
					preg_match($expr, $doc_comment, $match);
					if ($match) {
						/** @var $advice callable */
						$advice = array(Getter::class, 'get' . $match[1]);
					}
					else {
						trigger_error(
							'@link of ' . $property->class . '::' . $property->name
							. ' must be All, Collection, DateTime, Map or Object',
							E_USER_ERROR
						);
						$advice = null;
					}
					$properties[$property->name][] = array('read', $advice);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- scanForSetters
	/**
	 * @param $properties array
	 * @param $class      ReflectionClass
	 */
	private function scanForSetters(&$properties, ReflectionClass $class)
	{
		foreach ($class->getProperties() as $property) {
			if ($property->class == $class->name) {
				$doc_comment = $property->getDocComment();
				preg_match('%\n\s+\*\s+@setter(?:\s+(?:(\w+)::)?(\w+)?)?%', $doc_comment, $match);
				if ($match) {
					$advice = array(
						empty($match[1]) ? '$this' : $match[1],
						isset($match[2]) ? $match[2] : Names::propertyToMethod($property->name, 'set')
					);
					$properties[$property->name][] = array('write', $advice);
				}
			}
		}
	}

	//--------------------------------------------------------------------------- willCompileFunction
	private function willCompileFunction()
	{
		trigger_error('Compiler does not know how to compile function joinpoints', E_USER_ERROR);
	}

}
