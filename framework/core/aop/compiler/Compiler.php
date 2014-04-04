<?php
namespace SAF\AOP;

use SAF\Framework\Dao;
use SAF\Framework\Dependency;
use SAF\Framework\Getter;
use SAF\Framework\ICompiler;
use SAF\Framework\Names;
use SAF\Framework\Php_Class;
use SAF\Framework\Php_Compiler;
use SAF\Framework\Php_Source;
use SAF\Framework\Search_Object;
use SAF\Framework\Session;
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

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source   Php_Source
	 * @param $compiler Php_Compiler
	 * @return boolean
	 */
	public function compile(Php_Source $source, Php_Compiler $compiler = null)
	{
		$classes = $source->getClasses();
		if ($class = reset($classes)) {
			$class = Php_Class::fromPhpSource($source);
			if ($this->compileClass($class)) {
				$source->setSource($class->source);
				return true;
			}
		}
		return false;
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * @param $class Php_Class
	 * @return boolean
	 */
	public function compileClass(Php_Class $class)
	{
		$this->compiled_classes[$class->name] = true;
		if (self::DEBUG) echo '<h2>' . $class->name . '</h2>';

		$methods    = [];
		$properties = [];
		if ($class->type !== 'interface') {
			// implements : _read_property, _write_property
			if ($class->type != 'trait') {
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
			$properties_compiler = new Properties_Compiler($class);
			$methods_code = $properties_compiler->compile($properties);
		}

		if (self::DEBUG && $methods) echo '<pre>methods = ' . print_r($methods, true) . '</pre>';

		$method_compiler = new Method_Compiler($class);
		foreach ($methods as $method_name => $advices) {
			$methods_code[$method_name] = $method_compiler->compile($method_name, $advices);
		}

		if ($methods_code) {
			ksort($methods_code);

			if (self::DEBUG && $methods_code) echo '<pre>' . print_r($methods_code, true) . '</pre>';

			$class->source =
				substr($class->source, 0, -2) . TAB . '//' . str_repeat('#', 91) . ' AOP' . LF
				. join('', $methods_code)
				. LF . '}' . LF;
		}

		return boolval($methods_code);
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * @param $sources Php_Source[]
	 * @return boolean
	 */
	public function moreSourcesToCompile(&$sources)
	{
		$added = false;
		/** @var $search Dependency */
		$search = Search_Object::create(Dependency::class);
		$search->type = Dependency::T_USES;
		foreach ($sources as $source) {
			foreach ($source->getClasses() as $class) {
				if ($class->type == T_TRAIT) {
					$search->dependency_name = $class->name;
					/** @var $dependency Dependency */
					foreach (Dao::search($search, Dependency::class) as $dependency) {
						if (!isset($sources[$dependency->file_name])) {
							$sources[$dependency->file_name] = new Php_Source($dependency->file_name);
							$added = true;
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
		return $this->compileClass(Php_Class::fromFile($file_name));
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
		return [$methods, $properties];
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
				$advice = [
					empty($match[1]) ? '$this' : $match[1],
					empty($match[2]) ? Names::propertyToMethod($property->name, 'get') : $match[2]
				];
				$properties[$property->name][] = ['read', $advice];
			}
		}
		foreach ($this->scanForOverrides($class->documentation, ['getter']) as $match) {
			$advice = [
				empty($match['class_name']) ? '$this' : $match['class_name'],
				empty($match['method_name'])
					? Names::propertyToMethod($match['property_name'], 'get') : $match['method_name']
			];
			$properties[$match['property_name']][] = ['read', $advice];
		}
	}

	//----------------------------------------------------------------------------------- scanForInit
	/**
	 * @param $properties array
	 * @param $class      Php_Class
	 */
	private function scanForImplements(&$properties, Php_Class $class)
	{
		// properties from the class and its direct traits
		$implemented_properties = $class->getProperties(['traits']);
		foreach ($implemented_properties as $property) {
			$expr = '%'
				. '\n\s+\*\s+'            // each line beginning by '* '
				. '@(getter|link|setter)' // 1 : AOP annotation
				. '(?:\s+'                // class name and method or function name are optional
				. '(?:([\\\\\w]+)::)?'    // 2 : class name (optional)
				. '(\w+)'                 // 3 : method or function name
				. ')?'                    // end of optional block
				. '%';
			preg_match_all($expr, $property->documentation, $match);
			foreach ($match[1] as $type) {
				$type = ($type == 'setter') ? 'write' : 'read';
				$properties[$property->name]['implements'][$type] = true;
			}
			if ($property->getParent()) {
				$properties[$property->name]['override'] = true;
			}
		}
		// properties overridden into the class and its direct traits
		$documentations = $class->getDocumentations(['traits']);
		foreach ($this->scanForOverrides($documentations) as $match) {
			$properties[$match['property_name']]['implements'][$match['type']] = true;
			if (!isset($implemented_properties[$match['property_name']])) {
				$property = $class->getProperties(['inherited'])[$match['property_name']];
				if (
					!strpos($property->documentation, '@getter')
					&& !strpos($property->documentation, '@link')
					&& !strpos($property->documentation, '@setter')
				) {
					$expr = '%@override\s+' . $match['property_name'] . '\s+.*(@getter|@link|@setter)%';
					preg_match($expr, $property->class->getDocumentations(), $match2);
					if ($match2) {
						$properties[$match['property_name']]['override'] = true;
					}
				}
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
		$disable = [];
		foreach ($properties as $property_name => $advices) {
			foreach ($advices as $key => $advice) if (is_numeric($key)) {
				if (is_array($advice) && (reset($advice) == 'read')) {
					$disable[$property_name] = true;
					break;
				}
			}
		}
		foreach ($class->getProperties() as $property) {
			if (!isset($disable[$property->name]) && strpos($property->documentation, '* @link')) {
				$expr = '%'
					. '\n\s+\*\s+'                           // each line beginning by '* '
					. '@link\s+'                             // link annotation
					. '(All|Collection|DateTime|Map|Object)' // 1 : link keyword
					. '%';
				preg_match($expr, $property->documentation, $match);
				if ($match) {
					$advice = [Getter::class, 'get' . $match[1]];
				}
				else {
					trigger_error(
						'@link of ' . $property->class->name . '::' . $property->name
						. ' must be All, Collection, DateTime, Map or Object',
						E_USER_ERROR
					);
					$advice = null;
				}
				$properties[$property->name][] = ['read', $advice];
			}
		}
		foreach ($this->scanForOverrides($class->documentation, ['link'], $disable) as $match) {
			$advice = [Getter::class, 'get' . $match['method_name']];
			$properties[$match['property_name']][] = ['read', $advice];
		}
	}

	//-------------------------------------------------------------------------------- scanForMethods
	/**
	 * @param $methods array
	 * @param $class   Php_Class
	 */
	/*
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

	//------------------------------------------------------------------------------ scanForOverrides
	/**
	 * @param $documentation string
	 * @param $annotations   string[]
	 * @param $disable       array
	 * @return array
	 */
	private function scanForOverrides(
		$documentation, $annotations = ['getter', 'link', 'setter'], $disable = []
	) {
		$overrides = [];
		if (strpos($documentation, '@override')) {
			$expr = '%'
				. '\n\s+\*\s+'               // each line beginning by '* '
				. '@override\s+'             // override annotation
				. '(\w+)\s+'                 // 1 : property name
				. '(?:'                      // begin annotations loop
				. '(?:@.*?\s+)?'             // others overridden annotations
				. '@annotation'           // overridden annotation
				. '(?:\s+(?:([\\\\\w]+)::)?' // 1 : class name
				. '(\w+)?)?'                 // 2 : method or function name
				. ')+'                       // end annotations loop
				. '%';
			foreach ($annotations as $annotation) {
				preg_match_all(str_replace('@annotation', '@' . $annotation, $expr), $documentation, $match);
				if ($match[1] && (($annotation != 'link') || !isset($disable[$match[1][0]]))) {
					if ($annotation == 'getter') {
						$disable[$match[1][0]] = true;
					}
					$type = ($annotation == 'setter') ? 'write' : 'read';
					$overrides[] = [
						'type'          => $type,
						'property_name' => $match[1][0],
						'class_name'    => $match[2][0],
						'method_name'   => $match[3][0]
					];
				}
			}
		}
		return $overrides;
	}

	//------------------------------------------------------------------------------- scanForReplaces
	/**
	 * @param $properties array
	 * @param $class      Php_Class
	 */
	private function scanForReplaces(&$properties, Php_Class $class)
	{
		foreach ($class->getProperties(['traits']) as $property) {
			$expr = '%'
				. '\n\s+\*\s+' // each line beginning by '* '
				. '@replaces\s+'  // alias annotation
				. '(\w+)'      // 1 : property name
				. '%';
			preg_match($expr, $property->documentation, $match);
			if ($match) {
				$properties[$match[1]]['replaced'] = $property->name;
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
				$advice = [
					empty($match[1]) ? '$this' : $match[1],
					empty($match[2]) ? Names::propertyToMethod($property->name, 'set') : $match[2]
				];
				$properties[$property->name][] = ['write', $advice];
			}
		}
		foreach ($this->scanForOverrides($class->documentation, ['setter']) as $match) {
			$advice = [
				empty($match['class_name']) ? '$this' : $match['class_name'],
				empty($match['method_name'])
					? Names::propertyToMethod($match['property_name'], 'set') : $match['method_name']
			];
			$properties[$match['property_name']][] = ['write', $advice];
		}
	}

}
