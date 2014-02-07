<?php
namespace SAF\AOP;

use ReflectionClass;
use ReflectionProperty;
use SAF\Framework\Application;
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
		if (self::DEBUG) echo "duration = " . (microtime(true) - $start_time) . "<br>";
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
						$class_name = $match[1] . "\\" . $class_name;
					}
					if (!isset($this->compiled_classes[$class_name])) {
						$this->compileClass($class_name, $file_name);
					}
					if (self::DEBUG) echo "- compile class $class_name<br>";
				}
				else {
					if (self::DEBUG) echo "<b>- nothing into $file_name</b><br>";
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
		if (self::DEBUG) echo "cleanup of $class_name = $cleanup<br>";

		if (isset($_GET['C'])) {
			echo "CLEANUP-ONLY $class_name<br>";
			$methods = $properties = array();
		}
		else {

			if (self::DEBUG) echo "<h2>compile class $class_name</h2>";
			$buffer = substr($buffer, 0, -2) . "\t//" . str_repeat('#', 91) . " AOP\n";

			$properties = array();
			if (!$class->isInterface() && !$class->isTrait()) {
				$this->scanForLinks($properties,   $class);
				$this->scanForGetters($properties, $class);
				$this->scanForSetters($properties, $class);
			}

			list($methods, $properties2) = $this->getPointcuts($class_name);
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
			if (isset($_GET['D'])) echo "<pre>" . htmlentities($buffer) . "</pre>";
			if (isset($_GET['R'])) echo "READ-ONLY $class_name<br>";
			else file_put_contents($file_name, $buffer);
			if (self::DEBUG) echo "<pre>" . htmlentities($buffer) . "</pre>";
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
	 * @param $property ReflectionProperty
	 * @param $class    ReflectionClass
	 * @return boolean
	 */
	private function isPropertyInClass(ReflectionProperty $property, ReflectionClass $class)
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
		if (self::DEBUG) echo "&gt; Traits for $class->name are " . print_r($traits, true) . "<br>";
		return in_array($property->class, $traits);
	}

	//-------------------------------------------------------------------------------- scanForGetters
	/**
	 * @param $properties array
	 * @param $class      ReflectionClass
	 */
	private function scanForGetters(&$properties, ReflectionClass $class)
	{
		foreach ($class->getProperties() as $property) {
			$doc_comment = $property->getDocComment();
			if (strpos($doc_comment, '@getter') && $this->isPropertyInClass($property, $class)) {
				preg_match('/@getter\s+([^\s\n]*)\n/', $doc_comment, $match);
				$getter = ($match) ? $match[1] : Names::propertyToMethod($property->name, 'get');
				// todo Aop getters, Class_Name::methodName getters
				$properties[$property->name][] = array("read", array('$this', $getter));
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
			$doc_comment = $property->getDocComment();
			if (strpos($doc_comment, '* @link') && $this->isPropertyInClass($property, $class)) {
				preg_match('/\*\s+@link\s+(All|Collection|Map|Object)\s*\n/', $doc_comment, $match);
				if ($match) {
					if ($match[1] == 'All') {
						$advice = array('SAF\Framework\Getter', 'getAll');
					}
					elseif ($match[1] == 'Collection') {
						$advice = array('SAF\Framework\Getter', 'getCollection');
					}
					elseif ($match[1] == 'Map') {
						$advice = array('SAF\Framework\Getter', 'getMap');
					}
					else {
						$advice = array('SAF\Framework\Getter', 'getObject');
					}
				}
				else {
					trigger_error(
						'Link must be Collection, Map or Object for '
							. $property->class . '::' . $property->name,
						E_USER_ERROR
					);
					$advice = null;
				}
				$properties[$property->name][] = array("read", $advice);
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
			$doc_comment = $property->getDocComment();
			if (strpos($doc_comment, '@setter') && $this->isPropertyInClass($property, $class)) {
				preg_match('/@setter\s+([^\s\n]*)\n/', $doc_comment, $match);
				$getter = ($match) ? $match[1] : Names::propertyToMethod($property->name, 'set');
				// todo Aop getters, Class_Name::methodName getters
				$properties[$property->name][] = array("write", array('$this', $getter));
			}
		}
	}

	//--------------------------------------------------------------------------- willCompileFunction
	private function willCompileFunction()
	{
		trigger_error('Compiler does not know how to compile function joinpoints', E_USER_ERROR);
	}

}
