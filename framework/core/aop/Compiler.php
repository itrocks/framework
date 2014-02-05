<?php
namespace SAF\AOP;

use SAF\Framework\Application;
use SAF\Framework\Reflection_Class;
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
	 */
	private function cleanup(&$buffer)
	{
		// remove all "\r"
		$buffer = trim(str_replace("\r", '', $buffer));
		// remove since the line containing "//#### AOP" until the end of the file
		$expr = '`\n\s*//#+\s+AOP.*}([\s*\n]*\})[\s*\n]*`s';
		$buffer = preg_replace($expr, '$1', $buffer) . "\n";
		// replace "/* public */ private [static] function name_(" by "public [static] function name("
		$expr = '`(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)((private|protected|public)\s*)?'
			. '(static\s*)?function(\s+\w*)\_\s*\(`';
		$buffer = preg_replace($expr, '$1$2$3$6function$7(', $buffer);
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
					if (!$this->compiled_classes[$class_name]) {
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
	 * @param $class_name string
	 * @param $methods    array
	 * @param $properties array
	 * @param $file_name  string
	 */
	private function compileClass($class_name, $methods, $properties, $file_name = null)
	{
		if (isset($file_name)) {
			/** @noinspection PhpIncludeInspection */
			include_once $file_name;
		}
		else {
			$file_name = (new Reflection_Class($class_name))->getFileName();
		}
		$buffer = file_get_contents($file_name);
		$this->cleanup($buffer);

		if (isset($_GET['C'])) echo "CLEANUP-ONLY $class_name<br>"; else {

		if (self::DEBUG) echo "<h2>compile class $class_name</h2>";
		$buffer = substr($buffer, 0, -2) . "\t//" . str_repeat('#', 91) . " AOP\n";

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

		if (isset($_GET['D'])) echo "<pre>" . htmlentities($buffer) . "</pre>";
		if (isset($_GET['R'])) echo "READ-ONLY $class_name<br>"; else
		file_put_contents($file_name, $buffer);

		if (self::DEBUG) echo "<pre>" . htmlentities($buffer) . "</pre>";

		$this->compiled_classes[$class_name] = true;
	}

	//--------------------------------------------------------------------------- willCompileFunction
	private function willCompileFunction()
	{
		trigger_error('Compiler does not know how to compile function joinpoints', E_USER_ERROR);
	}

}
