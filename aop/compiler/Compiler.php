<?php
namespace ITRocks\Framework\AOP;

use ITRocks\Framework\AOP\Compiler\Scanners;
use ITRocks\Framework\AOP\Weaver\Handler;
use ITRocks\Framework\AOP\Weaver\IWeaver;
use ITRocks\Framework\Builder\Class_Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Needs_Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\PHP;
use ITRocks\Framework\PHP\Compiler\More_Sources;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\Done_Compiler;
use ITRocks\Framework\PHP\ICompiler;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Reflection\Annotation\Property\Getter_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Session;

/**
 * Standard aspect weaver compiler
 */
class Compiler implements Done_Compiler, ICompiler, Needs_Main
{
	use Scanners;

	//----------------------------------------------------------------------------------------- DEBUG
	const DEBUG = false;

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

	//---------------------------------------------------------------------------------- addPointcuts
	/**
	 * @param $methods        array
	 * @param $properties     array
	 * @param $class_name     string
	 * @param $handler_filter string[] @values after, around, before
	 */
	private function addPointcuts(
		array &$methods, array &$properties, $class_name, array $handler_filter = null
	) {
		foreach ($this->weaver->getJoinpoints($class_name) as $method_or_property => $pointcuts2) {
			foreach ($pointcuts2 as $pointcut) {
				if (empty($handler_filter) || in_array($pointcut[0], $handler_filter)) {
					if ($pointcut[0] === Handler::READ) {
						$properties[$method_or_property]['implements'][Handler::READ] = true;
						$properties[$method_or_property][] = $pointcut;
					}
					elseif ($pointcut[0] === Handler::WRITE) {
						$properties[$method_or_property]['implements'][Handler::WRITE] = true;
						$properties[$method_or_property][] = $pointcut;
					}
					else {
						$methods[$method_or_property][] = $pointcut;
					}
				}
			}
		}
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
		if (self::DEBUG) { echo '<h2>' . $class->name . '</h2>'; flush(); }

		// if source has already been compiled for AOP, then do not compile it again
		preg_match('|\n\s+//#+\sAOP\n|', $class->source->getSource(), $matches);
		if ($matches) {
			if (self::DEBUG) echo 'Do not compile again.' . BR;
			return false;
		}

		$methods    = [];
		$properties = [];

		$interfaces = $class->getInterfaceNames();
		foreach ($interfaces as $interface_name) {
			$this->addPointcuts(
				$methods, $properties, $interface_name, [Handler::AROUND, Handler::BEFORE]
			);
		}
		$this->addPointcuts($methods, $properties, $class->name);
		foreach ($interfaces as $interface_name) {
			$this->addPointcuts($methods, $properties, $interface_name, [Handler::AFTER]);
		}

		if ($class->type !== T_INTERFACE) {
			// implements : _read_property, _write_property
			if ($class->type !== T_TRAIT) {
				$this->scanForImplements($properties, $class);
			}
			// read/write : __aop, __construct, __get, __isset, __set, __unset
			$this->scanForDefaults($properties, $class);
			$this->scanForGetters ($properties, $class);
			$this->scanForLinks   ($properties, $class);
			$this->scanForSetters ($properties, $class);
			$this->scanForReplaces($properties, $class);
			$this->scanForAbstract($methods,    $class);
			// TODO should be done for all classes before compiling : it creates links in other classes
			//$this->scanForMethods($methods, $class);
		}

		$methods_code = [];

		if (self::DEBUG && $properties) {
			echo '<pre>properties = ' . print_r($properties, true) . '</pre>';
			flush();
		}

		if ($properties) {
			$properties_compiler = new Compiler\Properties($class);
			$methods_code = $properties_compiler->compile($properties);
		}

		if (self::DEBUG && $methods) {
			echo '<pre>methods = ' . print_r($methods, true) . '</pre>';
			flush();
		}

		$method_compiler = new Compiler\Method($class);
		foreach ($methods as $method_name => $advices) {
			if ($compiled_method = $method_compiler->compile($method_name, $advices)) {
				$methods_code[$method_name] = $compiled_method;
			}
		}

		if ($methods_code) {
			ksort($methods_code);

			if (self::DEBUG && $methods_code) {
				echo '<pre>methods_code = ' . print_r($methods_code, true) . '</pre>';
				flush();
			}

			$class->source->setSource(
				substr(trim($class->source->getSource()), 0, -1)
				. TAB . '//' . str_repeat('#', 91) . ' AOP' . LF
				. join('', $methods_code)
				. LF . '}' . LF
			);
		}

		return boolval($methods_code);
	}

	//----------------------------------------------------------------------------------- doneCompile
	public function doneCompile()
	{
		$this->weaver->backupFile();
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * @param $more_sources More_Sources
	 */
	public function moreSourcesToCompile(More_Sources $more_sources)
	{
		// search into dependencies : used classes
		$search = ['type' => Dependency::T_USE];
		foreach ($more_sources->sources as $source) {
			foreach ($source->getClasses() as $class) {
				if ($class->type === T_TRAIT) {
					$search['dependency_name'] = Func::equal($class->name);
					foreach (Dao::search($search, Dependency::class) as $dependency) {
						/** @var $dependency Dependency */
						while ($dependency && Class_Builder::isBuilt($dependency->class_name)) {
							$search_built_parent = Search_Object::create(Dependency::class);
							$search_built_parent->class_name = $dependency->class_name;
							$search_built_parent->type       = [Dependency::T_EXTENDS, Dependency::T_USE];
							$dependency = Dao::searchOne($search_built_parent);
							if (!$dependency) {
								trigger_error(
									'No parent class for built class ' . $search_built_parent->class_name,
									E_USER_WARNING
								);
							}
							$search_built_parent->class_name = $dependency->dependency_name;
							$search_built_parent->type       = Dependency::T_DECLARATION;
							$dependency = Dao::searchOne($search_built_parent);
							if (!$dependency) {
								trigger_error(
									'No "declaration" dependency for class ' . $search_built_parent->class_name,
									E_USER_ERROR
								);
							}
						}
						if (!isset($more_sources->sources[$dependency->file_name])) {
							$source = Reflection_Source::ofFile($dependency->file_name, $dependency->class_name);
							$more_sources->add(
								$source, $source->getFirstClassName(), $dependency->file_name, true
							);
						}
					}
				}
			}
		}

		// search into dependencies : registered methods
		foreach ($this->weaver->changedClassNames() as $class_name) {
			$source = Reflection_Source::ofClass($class_name);
			if (!isset($more_sources->sources[$source->file_name])) {
				$more_sources->add($source, $source->getFirstClassName(), $source->file_name, true);
			}
		}
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
	 * @param $class       Interfaces\Reflection_Class
	 * @param $only_method string Internal use only : the method name we are up-scanning
	 */
	private function scanForAbstract(
		array &$methods, Interfaces\Reflection_Class $class, $only_method = null
	) {
		if ($class instanceof Reflection_Class && $class->getParentName()) {
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

	//----------------------------------------------------------------------------- scanForImplements
	/**
	 * @param $properties array
	 * @param $class      Reflection_Class
	 */
	private function scanForImplements(array &$properties, Reflection_Class $class)
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
				$type = ($type === 'setter') ? Handler::WRITE : Handler::READ;
				$properties[$property->name]['implements'][$type] = true;
			}
			if ($property->getParent()) {
				$properties[$property->name]['override'] = true;
			}
		}
		// properties overridden into the class and its direct traits
		$documentations = $class->getDocComment([T_USE]);
		$annotations    = [Getter_Annotation::ANNOTATION, Link_Annotation::ANNOTATION, 'setter'];
		foreach ($this->scanForOverrides($documentations, $annotations) as $match) {
			$properties[$match['property_name']]['implements'][$match['type']] = true;
			if (!isset($implemented_properties[$match['property_name']])) {
				$class_properties = $class->getProperties([T_EXTENDS]);
				$extends          = $class;
				while (!isset($class_properties[$match['property_name']])) {
					$extends_values = $extends->getListAnnotation('extends')->values();
					if (!$extends_values) {
						break;
					}
					$extends          = $extends->source->getOutsideClass(reset($extends_values));
					$class_properties = $extends->getProperties([T_EXTENDS]);
				}
				if (!isset($class_properties[$match['property_name']])) {
					continue;
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
					preg_match($expr, $property->class->getDocComment([]), $match2);
					if ($match2) {
						$properties[$match['property_name']]['override'] = true;
					}
				}
			}
		}
	}

	/**
	 * @param $methods array
	 * @param $class   Reflection_Class
	 */
	/*
	private function scanForMethods(array &$methods, Reflection_Class $class)
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

	//----------------------------------------------------------------------------- setMainController
	/**
	 * @param $main_controller Main
	 */
	public function setMainController(Main $main_controller)
	{
		// AOP compiler needs all plugins to be registered again, in order to build the complete
		// weaver's advices tree
		if (!$this->weaver->hasJoinpoints()) {
			$this->weaver->loadJoinpoints($this->weaver->defaultFileName());
		}
	}

}
