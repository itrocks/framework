<?php
namespace SAF\Framework\AOP;

use SAF\Framework\AOP\Compiler\Scanners;
use SAF\Framework\AOP\Weaver\IWeaver;
use SAF\Framework\Application;
use SAF\Framework\Builder;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Needs_Main;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Func;
use SAF\Framework\Mapper\Search_Object;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Session;
use SAF\Framework\PHP;
use SAF\Framework\PHP\Dependency;
use SAF\Framework\PHP\ICompiler;
use SAF\Framework\PHP\Reflection_Class;
use SAF\Framework\PHP\Reflection_Source;

/**
 * Standard aspect weaver compiler
 */
class Compiler implements ICompiler, Needs_Main
{
	use Scanners;

	const DEBUG = false;

	//----------------------------------------------------------------------------- $compiled_classes
	/**
	 * @var boolean[] key is class name, value is always true
	 */
	private $compiled_classes = [];

	//--------------------------------------------------------------------------------------- $weaver
	/**
	 * @var Weaver
	 */
	private $weaver;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $weaver IWeaver If not set, the current weaver plugin is used
	 */
	public function __construct(IWeaver $weaver = null)
	{
		$this->weaver = $weaver ?: Session::current()->plugins->get(Weaver::class);
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $buffer string
	 * @return boolean true if cleanup was necessary, false if buffer was clean before cleanup
	 */
	private static function cleanup(&$buffer)
	{
		// remove all '\r'
		$buffer = trim(str_replace(CR, '', $buffer));
		// remove since the line containing '//#### AOP' until the end of the file
		$expr = '%\n\s*//\#+\s+AOP.*%s';
		preg_match($expr, $buffer, $match1);
		$buffer = preg_replace($expr, '$1', $buffer) . ($match1 ? LF . LF . '}' . LF : LF);
		// replace '/* public */ private [static] function name_?(' by 'public [static] function name('
		$expr = '%'
			. '(?:\n\s*/\*\*?\s+@noinspection\s+PhpUnusedPrivateMethodInspection.*?\*/)?'
			. '(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)' // 1 2 3
			. '(?:(?:private|protected|public)\s+)?'
			. '(static\s+)?' // 4
			. 'function\s*(\s?\&\s?)?\s*(\w+)\_[0-9]*\s*' // 5 6
			. '\('
			. '%';

		preg_match($expr, $buffer, $match2);
		$buffer = preg_replace($expr, '$1$2$3$4function $5$6(', $buffer);
		return $match1 || $match2;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source   Reflection_Source
	 * @param $compiler PHP\Compiler
	 * @return boolean
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null)
	{
		$classes = $source->getClasses();
		if ($class = reset($classes)) {
			if ($this->compileClass($class)) {
				return true;
			}
		}
		return false;
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * @param $class Reflection_Class
	 * @return boolean
	 */
	public function compileClass(Reflection_Class $class)
	{
		$this->compiled_classes[$class->name] = true;
		if (self::DEBUG) echo '<h2>' . $class->name . '</h2>';

		$methods    = [];
		$properties = [];
		if ($class->type !== T_INTERFACE) {
			// implements : _read_property, _write_property
			if ($class->type !== T_TRAIT) {
				$this->scanForImplements($properties, $class);
			}
			// read/write : __aop, __construct, __get, __isset, __set, __unset
			$this->scanForGetters ($properties, $class);
			$this->scanForLinks   ($properties, $class);
			$this->scanForSetters ($properties, $class);
			$this->scanForReplaces($properties, $class);
			$this->scanForAbstract($methods,    $class);
			// TODO should be done for all classes before compiling : it creates links in other classes
			//$this->scanForMethods($methods, $class);
		}

		list($methods2, $properties2) = $this->getPointcuts($class->name);
		$methods    = arrayMergeRecursive($methods,    $methods2);
		$properties = arrayMergeRecursive($properties, $properties2);
		$methods_code = [];

		if (self::DEBUG && $properties) echo '<pre>properties = ' . print_r($properties, true) . '</pre>';

		if ($properties) {
			$properties_compiler = new Compiler\Properties($class);
			$methods_code = $properties_compiler->compile($properties);
		}

		if (self::DEBUG && $methods) echo '<pre>methods = ' . print_r($methods, true) . '</pre>';

		$method_compiler = new Compiler\Method($class);
		foreach ($methods as $method_name => $advices) {
			if ($compiled_method = $method_compiler->compile($method_name, $advices)) {
				$methods_code[$method_name] = $compiled_method;
			}
		}

		if ($methods_code) {
			ksort($methods_code);

			if (self::DEBUG && $methods_code) echo '<pre>' . print_r($methods_code, true) . '</pre>';

			$class->source->setSource(
				substr(trim($class->source->getSource()), 0, -1)
				. TAB . '//' . str_repeat('#', 91) . ' AOP' . LF
				. join('', $methods_code)
				. LF . '}' . LF
			);
		}

		return boolval($methods_code);
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * @param $sources Reflection_Source[]
	 * @return Reflection_Source[] added sources list
	 */
	public function moreSourcesToCompile(&$sources)
	{
		$added = [];

		// search into dependencies : used classes
		/** @var $search Dependency */
		$search = Search_Object::create(Dependency::class);
		$search->type = Dependency::T_USE;
		foreach ($sources as $source) {
			foreach ($source->getClasses() as $class) {
				if ($class->type === T_TRAIT) {
					$search->dependency_name = $class->name;
					foreach (Dao::search($search, Dependency::class) as $dependency) {
						while ($dependency && Builder::isBuilt($dependency->class_name)) {
							$search_built_parent = Search_Object::create(Dependency::class);
							$search_built_parent->class_name = $dependency->class_name;
							$search_built_parent->type       = Dependency::T_EXTENDS;
							$dependency = Dao::searchOne($search_built_parent);
							if (!$dependency) {
								trigger_error(
									'Not parent class for built class ' . $search_built_parent->class_name,
									E_USER_ERROR
								);
							}
							$search_built_parent->class_name = $dependency->dependency_name;
							$search_built_parent->type       = Dependency::T_DECLARATION;
							$dependency = Dao::searchOne($search_built_parent);
							if (!$dependency) {
								trigger_error(
									'Not declaration dependency for class ' . $search_built_parent->class_name,
									E_USER_ERROR
								);
							}
						}
						/** @var $dependency Dependency */
						if (!isset($sources[$dependency->file_name])) {
							$source = new Reflection_Source($dependency->file_name);
							$sources[$dependency->file_name] = $source;
							$added[$dependency->file_name]   = $source;
						}
					}
				}
			}
		}

		// search into dependencies : registered methods
		foreach ($sources as $source) {
			$search->file_name = $source->file_name;
			$search->dependency_name = Registerable::class;
			$search->type = Dependency::T_IMPLEMENTS;
			if (Dao::searchOne($search, Dependency::class)) {
				unset($search->dependency_name);
				$search->type = Dependency::T_CLASS;
				foreach (Dao::search($search, Dependency::class) as $dependency) {
					$source = Reflection_Source::of($dependency->dependency_name);
					if (!isset($sources[$source->file_name])) {
						$sources[$source->file_name] = $source;
						$added[$source->file_name] = $source;
					}
				}
			}
		}

		// classes that are already into $sources
		$already = [];
		foreach ($sources as $source) {
			foreach ($source->getClasses() as $class) {
				$already[$class->name] = true;
			}
		}

		// search into advices and add sources that have sources to compile as advice
		foreach ($this->weaver->getJoinpoints() as $class_name => $joinpoint) {
			if (!isset($already[$class_name])) {
				foreach ($joinpoint as $advices) {
					foreach ($advices as $advice) {
						if (is_array($advice = $advice[1])) {
							$advice_class = $advice[0];
							if (is_object($advice_class)) {
								$advice_class = get_class($advice_class);
							}
							if (isset($already[$advice_class])) {
								$source = Reflection_Source::of($class_name);
								if ($source->getClass($class_name)) {
									$sources[$source->file_name] = $source;
									$added[$source->file_name] = $source;
									$already[$class_name] = true;
								}
								else {
									trigger_error(
										'No class ' . $class_name . ' into file ' . $source->file_name,
										E_USER_ERROR
									);
								}
							}
						}
					}
				}
			}
		}

		return $added;
	}

	//----------------------------------------------------------------------------------- compileFile
	/**
	 * @param $file_name string
	 * @return boolean
	 */
	public function compileFile($file_name)
	{
		return $this->compileClass((new Reflection_Source($file_name))->getClasses()[0]);
	}

	//---------------------------------------------------------------------------------- getPointcuts
	/**
	 * @param $class_name string
	 * @return array[] two elements : [$methods, $properties)
	 */
	private function getPointcuts($class_name)
	{
		$methods    = [];
		$properties = [];
		foreach ($this->weaver->getJoinpoints($class_name) as $method_or_property => $pointcuts2) {
			foreach ($pointcuts2 as $pointcut) {
				if ($pointcut[0] == 'read') {
					$properties[$method_or_property]['implements']['read'] = true;
					$properties[$method_or_property][] = $pointcut;
				}
				elseif ($pointcut[0] == 'write') {
					$properties[$method_or_property]['implements']['write'] = true;
					$properties[$method_or_property][] = $pointcut;
				}
				else {
					$methods[$method_or_property][] = $pointcut;
				}
			}
		}
		return [$methods, $properties];
	}

	//------------------------------------------------------------------------------- scanForAbstract
	/**
	 * Scan weaver for all parent AOP aspects on abstract methods
	 * - for each methods implemented in the class or its traits
	 * - for each parent abstract method of these methods
	 * - for all the parent chain between the method and its parent
	 * - if any advice : add it for the current class
	 *
	 * @param $methods     array [$method][$index] = [$type, callback $advice]
	 * @param $class       Reflection_Class
	 * @param $only_method string Internal use only : the method name we are up-scanning
	 */
	private function scanForAbstract(&$methods, Reflection_Class $class, $only_method = null)
	{
		if ($class->getParentName()) {
			$parent_class = $class->getParentClass();
			$parent_methods = $parent_class->getMethods([T_EXTENDS, T_IMPLEMENTS]);
			foreach ($class->getMethods($only_method ? [T_EXTENDS, T_IMPLEMENTS] : [T_USE]) as $method) {
				if (!$only_method || ($only_method === $method->name)) {
					if (
						isset($parent_methods[$method->name])
						&& $parent_methods[$method->name]->isAbstract()
					) {
						$this->scanForAbstract($methods, $parent_class, $method->name);
						$joinpoints = $this->weaver->getJoinpoint([$parent_class->name, $method->name]);
						foreach ($joinpoints as $pointcut) {
							$methods[$method->name][] = $pointcut;
						}
					}
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- scanForInit
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForImplements(&$properties, Reflection_Class $class)
	{
		// properties from the class and its direct traits
		$implemented_properties = $class->getProperties([T_USE]);
		foreach ($implemented_properties as $property) {
			$expr = '%'
				. '\n\s+\*\s+'            // each line beginning by '* '
				. '@(getter|link|setter)' // 1 : AOP annotation
				. '(?:\s+'                // class name and method or function name are optional
				. '(?:([\\\\\w]+)::)?'    // 2 : class name (optional)
				. '(\w+)'                 // 3 : method or function name
				. ')?'                    // end of optional block
				. '%';
			preg_match_all($expr, $property->getDocComment(), $match);
			foreach ($match[1] as $type) {
				$type = ($type == 'setter') ? 'write' : 'read';
				$properties[$property->name]['implements'][$type] = true;
			}
			if ($property->getParent()) {
				$properties[$property->name]['override'] = true;
			}
		}
		// properties overridden into the class and its direct traits
		$documentations = $class->getDocComment([T_USE]);
		foreach ($this->scanForOverrides($documentations) as $match) {
			$properties[$match['property_name']]['implements'][$match['type']] = true;
			if (!isset($implemented_properties[$match['property_name']])) {
				$class_properties = $class->getProperties([T_EXTENDS]);
				$extends = $class;
				while (!isset($class_properties[$match['property_name']])) {
					$extends = $extends->source->getOutsideClass(
						$extends->getListAnnotation('extends')->values()[0]
					);
					$class_properties = $extends->getProperties([T_EXTENDS]);
				}
				$property = $class_properties[$match['property_name']];
				if (isset($extends)) {
					$property->final_class = $class->name;
				}
				if (
					!strpos($property->getDocComment(), '@getter')
					&& !strpos($property->getDocComment(), '@link')
					&& !strpos($property->getDocComment(), '@setter')
				) {
					$expr = '%@override\s+' . $match['property_name'] . '\s+.*(@getter|@link|@setter)%';
					preg_match($expr, $property->class->getDocComment(), $match2);
					if ($match2) {
						$properties[$match['property_name']]['override'] = true;
					}
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- scanForMethods
	/**
	 * @param $methods array
	 * @param $class   Reflection_Class
	 */
	/*
	private function scanForMethods(&$methods, Reflection_Class $class)
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
						$aspect = [$type, [$method->class->name, $method->name]];
						if ($has_this) {
							$aspect[] = $has_this;
						}
						$methods[$class_name][$method_name] = $aspect;
					}
				}
			}
		}
	}
	*/

	//------------------------------------------------------------------------------- scanForReplaces
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForReplaces(&$properties, Reflection_Class $class)
	{
		foreach ($class->getProperties([T_USE]) as $property) {
			$expr = '%'
				. '\n\s+\*\s+'   // each line beginning by '* '
				. '@replaces\s+' // alias annotation
				. '(\w+)'        // 1 : property name
				. '%';
			preg_match($expr, $property->getDocComment(), $match);
			if ($match) {
				$properties[$match[1]]['replaced'] = $property->name;
			}
		}
	}

	//----------------------------------------------------------------------------- setMainController
	/**
	 * @param $main_controller Main
	 */
	public function setMainController(Main $main_controller)
	{
		// AOP compiler needs all plugins to be registered again, in order to build the complete
		// weaver's advices tree
		if (!$this->weaver->hasJoinpoints()) {
			$this->weaver->loadJoinpoints(Application::current()->getCacheDir() . SL . 'weaver.php');
		}
	}

}
