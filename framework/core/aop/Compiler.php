<?php
namespace SAF\AOP;

use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Method;

/**
 * Standard aspect weaver compiler
 */
class Compiler implements ICompiler
{

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $buffer string
	 */
	private function cleanup(&$buffer)
	{
		// remove all "\r"
		$buffer = str_replace("\r", "", $buffer);
		// remove since the line containing "//#### AOP" until the end of the file
		$expr = '`\n\s*//#+\s+AOP.*(\n}\n)`s';
		$buffer = preg_replace($expr, '$1', $buffer);
		// replace "/* public */ private [static] function name_(" by "public [static] function name("
		$expr = '`(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)((private|protected|public)\s*)?'
			. '(static\s*)?function(\s+\w*)\_\s*\(`';
		$buffer = preg_replace($expr, '$1$2$3$6function$7(', $buffer);
		//echo "<pre>" . htmlentities($buffer) . "</pre>";
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $weaver IWeaver
	 */
	public function compile(IWeaver $weaver)
	{
		if (!($weaver instanceof Weaver)) {
			trigger_error("Compiler can only compile aspect weaver of class Weaver", E_USER_ERROR);
			return;
		}
		foreach ($weaver->getJoinpoints() as $joinpoint => $joinpoints1) {
			if (ctype_lower($joinpoint[0])) {
				$this->willCompileFunction($joinpoint, $joinpoints1);
			}
			else {
				$class_name = $joinpoint;
				$methods    = array();
				$properties = array();
				foreach ($joinpoints1 as $joinpoint2 => $joinpoints2) {
					foreach ($joinpoints2 as $type => $joinpoint) {
						if (($type == "read") || ($type == "write")) {
							$properties[$joinpoint2][$type][] = $joinpoint;
						}
						else {
							$methods[$joinpoint2][$type][] = $joinpoint;
						}
					}
				}
				$this->compileClass($class_name, $methods, $properties);
			}
		}
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * @param $class_name string
	 * @param $methods    array
	 * @param $properties array
	 */
	private function compileClass($class_name, $methods, $properties)
	{
		$file_name = (new Reflection_Class($class_name))->getFileName();
		include_once $file_name;
		$buffer = file_get_contents($file_name);
		$this->cleanup($buffer);
		// ...
		echo "<h2>compile class $class_name</h2>";

		foreach ($methods as $method_name => $advices) {
			$this->compileMethod($class_name, $method_name, $advices, $buffer);
		}
	}

	//--------------------------------------------------------------------------------- compileMethod
	/**
	 * @param $class_name  string
	 * @param $method_name string
	 * @param $advices     array
	 * @param $buffer      string
	 */
	public function compileMethod($class_name, $method_name, $advices, &$buffer)
	{
		echo "<h3>$class_name::$method_name</h3>";

		$source_method = new Reflection_Method(
			$class_name,
			method_exists($class_name, $method_name . '_') ? ($method_name . '_') : $method_name
		);
		$doc_comment = $source_method->getDocComment();
		preg_match(
			'`(\n\s*)((private|protected|public)\s*)?(static\s*)?function\s+\w*\s*\(`',
			$buffer,
			$match
		);
		$prototype = $match[0] . join(', ', $source_method->getParameters());

		echo "<pre>" . $doc_comment . "</pre>";
		echo "<pre>" . $prototype . ")\n{</pre>";
		echo "<pre>}</pre>";
	}

	//--------------------------------------------------------------------------- willCompileFunction
	private function willCompileFunction()
	{
		trigger_error("Compiler does not know how to compile function joinpoints, sorry", E_USER_ERROR);
	}

}
