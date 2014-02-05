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
	public $compiled_classes = array();

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $buffer string
	 * @return boolean
	 */
	private function cleanup(&$buffer)
	{
		// remove all "\r"
		$buffer = trim(str_replace("\r", '', $buffer));
		// remove since the line containing "//#### AOP" until the end of the file
		$expr = '`\n\s*//#+\s+AOP.*}([\s*\n]*\})[\s*\n]*`s';
		preg_match($expr, $buffer, $match1);
		$buffer = preg_replace($expr, '$1', $buffer) . "\n";
		// replace "/* public */ private [static] function name_(" by "public [static] function name("
		$expr = '`(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)((private|protected|public)\s*)?'
			. '(static\s*)?function(\s+\w*)\_\s*\(`';
		preg_match($expr, $buffer, $match2);
		$buffer = preg_replace($expr, '$1$2$3$6function$7(', $buffer);
		return $match2 || $match2;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $weaver IWeaver
	 */
	public function compile(IWeaver $weaver)
	{
		$start_time = microtime(true);
		if (!($weaver instanceof Weaver)) {
			trigger_error('Compiler can only compile aspect weaver of class Weaver', E_USER_ERROR);
			return;
		}
		foreach ($weaver->getJoinpoints() as $joinpoint => $pointcuts) {
			if (ctype_lower($joinpoint)) {
				$this->willCompileFunction($joinpoint, $pointcuts);
			}
			else {
				$class_name = $joinpoint;
				$methods    = array();
				$properties = array();
				foreach ($pointcuts as $joinpoint2 => $pointcuts2) {
					foreach ($pointcuts2 as $pointcut) {
						if (($pointcut[0] == 'read') || ($pointcut[0] == 'write')) {
							$properties[$joinpoint2] = $pointcuts2;
						}
						else {
							$methods[$joinpoint2] = $pointcuts2;
						}
					}
				}
				$this->compileClass($class_name, $methods, $properties);
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
		$this->compile($weaver);
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
						$this->compileClass($class_name, array(), array(), $file_name);
					}
					echo "- compile class $class_name<br>";
				}
				else {
					echo "<b>- nothing into $file_name</b><br>";
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * @param $class_name string the name of the class or trait to be compiled
	 * @param $methods    array  advices for each method
	 * @param $properties array  advices for each property
	 * @param $file_name  string file name (optional)
	 */
	private function compileClass($class_name, $methods, $properties, $file_name = null)
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
		$cleanup = $this->cleanup($buffer);
		echo "cleanup of $class_name = $cleanup<br>";

		if (isset($_GET['C'])) echo "CLEANUP-ONLY $class_name<br>"; else {

		if (self::DEBUG) echo "<h2>compile class $class_name</h2>";
		$buffer = substr($buffer, 0, -2) . "\t//" . str_repeat('#', 91) . " AOP\n";

		if (!$class->isInterface() && !$class->isTrait()) {
			$this->scanForLinks($properties, $class);
			$this->scanForGetters($properties, $class);
			$this->scanForSetters($properties, $class);
		}

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
			if (isset($_GET['R'])) echo "READ-ONLY $class_name<br>"; else
			file_put_contents($file_name, $buffer);
			if (self::DEBUG) echo "<pre>" . htmlentities($buffer) . "</pre>";
		}

		$this->compiled_classes[$class_name] = true;
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
		echo "&gt; Traits for $class->name are " . print_r($traits, true) . "<br>";
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
				$properties[$property->name][] = array("read", array('self', $getter));
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
				// todo Aop getters, Class_Name::methodName getters
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
				$properties[$property->name][] = array("write", array('self', $getter));
			}
		}
	}

	//--------------------------------------------------------------------------- willCompileFunction
	private function willCompileFunction()
	{
		trigger_error('Compiler does not know how to compile function joinpoints', E_USER_ERROR);
	}

}
