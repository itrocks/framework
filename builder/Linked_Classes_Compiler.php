<?php
namespace ITRocks\Framework\Builder;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\PHP;
use ITRocks\Framework\PHP\Compiler\More_Sources;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\ICompiler;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Tools\Namespaces;

/**
 * This compiles child classes that extend classes replaced by built classes :
 * They must extend the built class
 */
class Linked_Classes_Compiler implements ICompiler
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source   Reflection_Source the PHP source file object
	 * @param $compiler PHP\Compiler|null the main compiler
	 * @return boolean true if compilation process did something, else false
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null) : bool
	{
		$builder  = Builder::current();
		$compiled = false;
		foreach ($source->getClasses() as $class) {
			// replace extends with the built replacement class
			if (
				!Class_Builder::isBuilt($class->name)
				&& ($parent_class_name = $class->getParentOriginalClassName())
			) {
				$replacement_class_name = Builder::className($parent_class_name);
				if (
					($parent_class_name !== $replacement_class_name)
					&& (
						Class_Builder::isBuilt($replacement_class_name)
						|| $builder->isReplacement($replacement_class_name)
					)
					&& !$this->recursiveReplacement($class, $parent_class_name, $replacement_class_name)
				) {
					$this->compileClass($class, $replacement_class_name);
					$compiled = true;
				}
			}
			foreach ($class->getTraitNames() as $trait_name) {
				if (Class_Builder::isBuilt($trait_name)) {
					continue;
				}
				$replacement_trait_name = Builder::className($trait_name);
				if (
					($trait_name !== $replacement_trait_name)
					&& (
						Class_Builder::isBuilt($replacement_trait_name)
						|| $builder->isReplacement($replacement_trait_name)
					)
					&& !$this->recursiveReplacement($class, $trait_name, $replacement_trait_name)
				) {
					$this->compileUse($class, $trait_name, $replacement_trait_name);
					$compiled = true;
				}
			}
		}
		return $compiled;
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * Compile the class : replace extends and link (if exists) by $replacement_class_name
	 *
	 * @param $class                  Reflection_Class
	 * @param $replacement_class_name string
	 */
	protected function compileClass(Reflection_Class $class, string $replacement_class_name)
	{
		$extended         = null;
		$buffer           = $class->source->getSource();
		$short_class_name = Namespaces::shortClassName($class->name);
		$buffer           = preg_replace_callback(
			'%(\s+class\s+' . $short_class_name . '\s+extends\s+)([\\\\\w]+)(\s+)%',
			function ($match) use (&$extended, $replacement_class_name) {
				$extended = $match[2];
				return $match[1] . BS . $replacement_class_name . $match[3];
			},
			$buffer
		);
		if ($extended) {
			$buffer = preg_replace_callback(
				'%(\n\s+\*\s+@link\s+)(' . str_replace(BS, BS . BS, $extended) . ')(\s+)%',
				function ($match) use ($replacement_class_name) {
					return $match[1] . BS . $replacement_class_name . $match[3];
				},
				$buffer
			);
		}
		$class->source = $class->source->setSource($buffer);
	}

	//------------------------------------------------------------------------------------ compileUse
	/**
	 * Compile the class use clause : replace use $trait_name by use $replacement_trait_name
	 *
	 * @param $class                  Reflection_Class
	 * @param $trait_name             string
	 * @param $replacement_trait_name string
	 */
	protected function compileUse(
		Reflection_Class $class, string $trait_name, string $replacement_trait_name
	) {
		$buffer           = $class->source->getSource();
		$short_trait_name = $class->short_trait_names[$trait_name] ?? (BS . $trait_name);
		$buffer = preg_replace_callback(
			'%(\s+use\s+)(' . str_replace(BS, BS . BS, $short_trait_name) . ')([;\s])%',
			function($match) use ($replacement_trait_name) {
				return $match[1] . BS . $replacement_trait_name . $match[3];
			},
			$buffer
		);
		$class->source = $class->source->setSource($buffer);
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * When a class is compiled, all classes that extend it must be compiled too
	 *
	 * @param $more_sources More_Sources
	 */
	public function moreSourcesToCompile(More_Sources $more_sources)
	{
		// we will search all extends and use dependencies
		$search = ['type' => [Dependency::T_EXTENDS, Dependency::T_USE]];
		foreach ($more_sources->sources as $source) {
			foreach ($source->getClasses() as $class) {
				if (Class_Builder::isBuilt($class->name)) {
					continue;
				}
				// add all classes that extend source classes
				$search['dependency_name'] = Func::equal($class->name);
				foreach (Dao::search($search, Dependency::class) as $dependency) {
					/** @var $dependency Dependency */
					if (
						!isset($more_sources->sources[$dependency->file_name])
						&& !isset($more_sources->sources[$dependency->class_name])
						&& !Class_Builder::isBuilt($dependency->class_name)
					) {
						$more_sources->add(
							Reflection_Source::ofFile($dependency->file_name, $dependency->class_name),
							$dependency->class_name
						);
					}
				}
			}
		}
	}

	//-------------------------------------------------------------------------- recursiveReplacement
	/**
	 * Returns true if the class is a replaced class inherited from a class that is replaced too
	 *
	 * @param $class                  Reflection_Class
	 * @param $parent_class_name      string
	 * @param $replacement_class_name string
	 * @return boolean
	 */
	protected function recursiveReplacement(
		Reflection_Class $class, string $parent_class_name, string $replacement_class_name
	) : bool
	{
		if ($replacement_class_name !== $parent_class_name) {
			return Reflection_Source::ofClass($replacement_class_name)
				->getClass($replacement_class_name)
				->isA($class->name);
		}
		return false;
	}

}
