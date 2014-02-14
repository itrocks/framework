<?php
namespace SAF\AOP;

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
* @param $class_name string
	 */
	public function compile($class_name = null)
	{
		if ($class_name) {
			$class = Php_Class::fromClassName($class_name);
			if ($class) {
				$this->compileClass($class);
			}
			else {
				trigger_error('Class not found ' . $class_name, E_USER_ERROR);
			}
		}
		else {
			foreach (Application::current()->include_path->getSourceFiles() as $file_name) {
				if (substr($file_name, -4) == '.php') {
					$class = Php_Class::fromFile($file_name);
					if ($class) {
						if (self::DEBUG) echo '<h2>Compile ' . $file_name . ' : ' . $class->type . ' ' . $class->name . '</h2>';
						if (!isset($this->compiled_classes[$class->name])) {
							$this->compileClass($class);
						}
					}
					elseif (self::DEBUG) echo '<h2 style="color:red;">Nothing into ' . $file_name . '</h2>';
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * @param $class Php_Class
	 */
	private function compileClass(Php_Class $class)
	{
		$this->compiled_classes[$class->name] = true;
		if (self::DEBUG) echo '<h2>' . $class->name . '</h2>';

		if (isset($_GET['C'])) {
			echo 'CLEANUP ' . $class->name . '<br>';
			file_put_contents($class->file_name, $class->source);
			return;
		}

		$methods    = array();
		$properties = array();
		if ($class->type !== 'interface') {
			if ($class->type != 'trait') {
				$this->scanForImplements($properties, $class);
			}
			$this->scanForLinks   ($properties, $class);
			$this->scanForGetters ($properties, $class);
			$this->scanForSetters ($properties, $class);
			$this->scanForAbstract($methods,    $class);
			// TODO should be done for all classes before compiling : it creates links in other classes
			//$this->scanForMethods($methods, $class);
		}

		list($methods2, $properties2) = $this->getPointcuts($class->name);
		$methods    = arrayMergeRecursive($methods,    $methods2);
		$properties = arrayMergeRecursive($properties, $properties2);
		$methods_code = array();

		if (self::DEBUG && $properties) echo '<pre>properties = ' . print_r($properties, true) . '</pre>';

		if ($properties) {
			$properties_compiler = new Properties_Compiler($class);
			$methods_code = $properties_compiler->compile($properties);
		}

		if (self::DEBUG && $methods) echo '<pre>methods = ' . print_r($methods, true) . '</pre>';

		$method_compiler = new Method_Compiler($class);
		foreach ($methods as $method_name => $advices) {
			$methods_code[$method_name] = $method_compiler->compile($method_name, $advices);
		}

		ksort($methods_code);

		if (self::DEBUG && $methods_code) echo '<pre>' . print_r($methods_code, true) . '</pre>';

		$buffer =
			substr($class->source, 0, -2) . "\t//" . str_repeat('#', 91) . ' AOP' . "\n"
			. join('', $methods_code)
			. "\n}\n";
		if (!$class->clean || $methods_code) {
			if (isset($_GET['R'])) echo 'READ-ONLY ' . $class->name . '<br>';
			else file_put_contents($class->file_name, $buffer);
			if (self::DEBUG || $_GET['D']) echo '<pre>' . htmlentities($buffer) . '</pre>';
		}
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

	//------------------------------------------------------------------------------- scanForAbstract
	/**
	 * @param $methods array
	 * @param $class   Php_Class
	 */
	private function scanForAbstract(&$methods, Php_Class $class)
	{
		/**
		 * TODO Scan weaver for all parent AOP aspects on abstract methods
		 * - for each methods implemented in the class or its traits
		 * - for each parent abstract method of these methods
		 * - for all the parent chain between the method and its parent
		 * - if any advice : add it for the current class
		 *
		 * Aspects on abstract methods will not be weaved until it's done.
		 */
	}

	//-------------------------------------------------------------------------------- scanForGetters
	/**
	 * @param $properties array
	 * @param $class      Php_Class
	 */
	private function scanForGetters(&$properties, Php_Class $class)
	{
		foreach ($class->getProperties() as $property) {
			$expr = '%'
				. '\n\s+\*\s+'               // each line beginnig by '* '
				. '@getter'                  // getter annotation
				. '(?:\s+(?:([\\\\\w]+)::)?' // 1 : class name
				. '(\w+)?)?'                 // 2 : method or function name
				. '%';
			preg_match($expr, $property->documentation, $match);
			if ($match) {
				$advice = array(
					empty($match[1]) ? '$this' : $match[1],
					isset($match[2]) ? $match[2] : Names::propertyToMethod($property->name, 'get')
				);
				$properties[$property->name][] = array('read', $advice);
			}
		}
	}

	//----------------------------------------------------------------------------------- scanForInit
	/**
	 * @param $properties array
	 * @param $class      Php_Class
	 */
	private function scanForImplements(&$properties, Php_Class $class)
	{
		foreach ($class->getProperties(array('traits')) as $property) {
			$expr = '%'
				. '\n\s+\*\s+'               // each line beginnig by '* '
				. '@(getter|link|setter)'    // 1 : AOP annotation
				. '(?:\s+(?:([\\\\\w]+)::)?' // 2 : class name
				. '(\w+)?)?'                 // 3 : method or function name
				. '%';
			preg_match($expr, $property->documentation, $match);
			if ($match) {
				$type = ($match[1] == 'setter') ? 'write' : 'read';
				$properties[$property->name]['implements'][$type] = true;
			}
		}
	}

	//---------------------------------------------------------------------------------- scanForLinks
	/**
	 * @param $properties array
	 * @param $class      Php_Class
	 */
	private function scanForLinks(&$properties, Php_Class $class)
	{
		foreach ($class->getProperties() as $property) {
			if (
				strpos($property->documentation, '* @link')
				&& !strpos($property->documentation, '* @getter')
			) {
				$expr = '%'
					. '\n\s+\*\s+'                           // each line beginning by '* '
					. '@link\s+'                             // link annotation
					. '(All|Collection|DateTime|Map|Object)' // 1 : link keyword
					. '%';
				preg_match($expr, $property->documentation, $match);
				if ($match) {
					$advice = array(Getter::class, 'get' . $match[1]);
				}
				else {
					trigger_error(
						'@link of ' . $property->class->name . '::' . $property->name
						. ' must be All, Collection, DateTime, Map or Object',
						E_USER_ERROR
					);
					$advice = null;
				}
				$properties[$property->name][] = array('read', $advice);
			}
		}
	}

	//-------------------------------------------------------------------------------- scanForMethods
	/**
	 * @param $methods array
	 * @param $class   Php_Class
	 */
	private function scanForMethods(&$methods, Php_Class $class)
	{
		foreach ($class->getMethods() as $method) {
			if (!$method->isAbstract() && ($method->class->name == $class->name)) {
				$expr = '%'
					. '\n\s+\*\s+'                // each line beginning by '* '
					. '@(after|around|before)\s+' // 1 : aspect type
					. '(?:([\\\\\w]+)::)?'        // 2 : optional class name
					. '(\w+)\s*'                  // 3 : method or function name
					. '(?:\((\$this)\))?'         // 4 : optional '$this'
					. '%';
				preg_match_all($expr, $method->documentation, $match);
				if ($match) {
					foreach (array_keys($match[0]) as $key) {
						$type        = $match[1][$key];
						$class_name  = $match[2][$key] ?: '$this';
						$method_name = $match[3][$key];
						$has_this    = $match[4][$key];
						$aspect = array($type, array($method->class->name, $method->name));
						if ($has_this) {
							$aspect[] = $has_this;
						}
						$methods[$class_name][$method_name] = $aspect;
					}
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- scanForSetters
	/**
	 * @param $properties array
	 * @param $class      Php_Class
	 */
	private function scanForSetters(&$properties, Php_Class $class)
	{
		foreach ($class->getProperties() as $property) {
			$expr = '%'
				. '\n\s+\*\s+'               // each line beginnig by '* '
				. '@setter'                  // setter annotation
				. '(?:\s+(?:([\\\\\w]+)::)?' // 1 : class name
				. '(\w+)?)?'                 // 2 : method or function name
				. '%';
			preg_match($expr, $property->documentation, $match);
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
