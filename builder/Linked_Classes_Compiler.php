<?php
namespace ITRocks\Framework\Builder;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\PHP;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\ICompiler;
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
	 * @param $compiler PHP\Compiler the main compiler
	 * @return boolean true if compilation process did something, else false
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null)
	{
		$compiled = false;
		foreach ($source->getClasses() as $class) {
			// replace extends with the built replacement class
			if (!Builder::isBuilt($class->name)) {
				$parent_class_name = $class->getParentName();
				if ($parent_class_name) {
					$replacement_class_name = Builder::className($parent_class_name);
					if ($replacement_class_name != $parent_class_name) {
						$class_exists = class_exists($replacement_class_name, false);
						if (
							($class_exists && is_a($replacement_class_name, $parent_class_name))
							|| (
								!$class_exists
								&& Reflection_Source::ofClass($replacement_class_name)->getClass(
									$replacement_class_name
								)->isA($class->name)
							)
						) {
							$replacement_class_name = $parent_class_name;
						}
					}
					if (is_array($replacement_class_name)) {
						trigger_error("Replacement classes should all be compiled", E_USER_ERROR);
						$compiler->addSource($source);
					}
					elseif (
						($replacement_class_name !== $parent_class_name)
						&& Builder::isBuilt($replacement_class_name)
					) {
						$extended = null;
						$buffer = $source->getSource();
						$short_class_name = Namespaces::shortClassName($class->name);
						$buffer = preg_replace_callback(
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
						$source->setSource($buffer);
						$compiled = true;
					}
				}
			}
		}
		return $compiled;
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * When a class is compiled, all classes that extends it must be compiled too
	 *
	 * @param &$sources Reflection_Source[]
	 * @return Reflection_Source[] added sources list
	 */
	public function moreSourcesToCompile(array &$sources)
	{
		$added = [];
		// we will search all extends dependencies
		$search = ['type' => Dependency::T_EXTENDS];
		foreach ($sources as $source) {
			foreach ($source->getClasses() as $class) {
				if (!Builder::isBuilt($class->name)) {
					// add all classes that extend source classes
					$search['dependency_name'] = Func::equal($class->name);
					foreach (Dao::search($search, Dependency::class) as $dependency) {
						if (
							!isset($sources[$dependency->file_name])
							&& !Builder::isBuilt($dependency->class_name)
						) {
							$added[$dependency->class_name] = Reflection_Source::ofFile(
								$dependency->file_name, $dependency->class_name
							);
						}
					}
				}
			}
		}

		return $added;
	}

}
