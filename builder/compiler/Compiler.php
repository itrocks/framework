<?php
namespace ITRocks\Framework\Builder;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Needs_Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\ICompiler;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Session;

/**
 * Built classes compiler
 */
class Compiler implements ICompiler, Needs_Main
{

	//------------------------------------------------------------------------------ $main_controller
	/**
	 * @var $main_controller Main
	 */
	private $main_controller;

	//--------------------------------------------------------------------------------------- compile
	/**
	 * Compile built classes
	 *
	 * @param $source   Reflection_Source
	 * @param $compiler PHP\Compiler
	 * @return boolean
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null)
	{
		$builder        = Builder::current();
		$builder->build = false;
		$compiled       = false;
		foreach ($source->getClasses() as $class) {
			$replacement = $builder->getComposition($class->name);
			if (is_array($replacement)) {
				foreach (Class_Builder::build($class->name, $replacement, true) as $built_source) {
					$compiler->addSource((new Reflection_Source())->setSource('<?php' . LF . $built_source));
					$compiled = true;
				}
			}
		}
		$builder->build = true;
		if (endsWith($source->file_name, '/builder.php')) {
			(new Compiler\Configuration\Builder)->compile($source);
		}
		if (endsWith($source->file_name, '/config.php')) {
			(new Compiler\Configuration\Config)->compile($source);
		}
		if (endsWith($source->file_name, '/menu.php')) {
			(new Compiler\Configuration\Menu)->compile($source);
		}
		return $compiled;
	}

	//-------------------------------------------------------------------------- hasConfigurationFile
	/**
	 * Does $sources contain a configuration file ?
	 * ie lower case filename with file_path instead of class name as key.
	 *
	 * @param $sources Reflection_Source[] key is $class_name, or $file_path if no class
	 * @return boolean
	 */
	protected function hasConfigurationFile(array $sources)
	{
		foreach ($sources as $file_path => $source) {
			if (
				// TODO replace all this 'if' by rLastParse($file_path) === '/builder.php') when cut done
				(strpos($file_path, SL) !== false)
				&& ctype_lower(substr(rLastParse($file_path, SL), 0, 1))
			) {
				return true;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------- moreSourcesAdd
	/**
	 * @param $class_name string
	 * @param $sources    Reflection_Source[] key is $class_name, or $file_path if no class
	 * @param $added      Reflection_Source[]
	 */
	protected function moreSourcesAdd($class_name, array &$sources, array &$added)
	{
		/** @var $dependency Dependency */
		$dependency = Dao::searchOne(
			['class_name' => $class_name, 'dependency_name' => $class_name], Dependency::class
		);
		if (!isset($sources[$dependency->file_name])) {
			$source               = Reflection_Source::ofFile($dependency->file_name, $class_name);
			$sources[$class_name] = $source;
			$added[$class_name]   = $source;
		}
	}

	//------------------------------------------------------------------------ moreSourcesAddChildren
	/**
	 * @param $added   Reflection_Source[]
	 * @param $sources Reflection_Source[]
	 */
	protected function moreSourcesAddChildren(array &$added, array &$sources)
	{
		foreach ($added as $source) {
			$dependencies = Dao::search(
				[
					'dependency_name' => $source->getFirstClassName(),
					'type'            => [Dependency::T_EXTENDS, Dependency::T_USE, Dependency::T_IMPLEMENTS]
				],
				Dependency::class
			);
			foreach ($dependencies as $dependency) {
				if (!isset($sources[$dependency->file_name])) {
					$source = Reflection_Source::ofFile($dependency->file_name, $dependency->class_name);
					$sources[$dependency->class_name] = $source;
					$added[$dependency->class_name]   = $source;
				}
			}
		}
	}

	//---------------------------------------------------------------------- moreSourcesAddComposites
	/**
	 * When traits / interfaces referenced into builder.php are modified : compile the composite class
	 *
	 * @param $added   Reflection_Source[]
	 * @param $sources Reflection_Source[] key is $class_name, or $file_path if no class
	 */
	protected function moreSourcesAddComposites(array &$added, array &$sources)
	{
		foreach (Builder::current()->getCompositions() as $built_class_name => $composition) {
			if (is_string($composition)) {
				$composition = [$composition];
			}
			foreach ($composition as $component_class_name) {
				if (
					isset($sources[$component_class_name])
					&& !isset($sources[$built_class_name])
					&& !isset($added[$built_class_name])
				) {
					$this->moreSourcesAdd($built_class_name, $sources, $added);
				}
			}
		}
	}

	//------------------------------------------------------- moreSourcesAddModifiedOrNewCompositions
	/**
	 * Compositions in builder.php that were added or modified : compile composite class
	 *
	 * @param $added            Reflection_Source[]
	 * @param $sources          Reflection_Source[] key is $class_name, or $file_path if no class
	 * @param $old_compositions array string[string $composite_class_name][integer]
	 * @param $new_compositions array string[string $composite_class_name][integer]
	 */
	protected function moreSourcesAddModifiedOrNewCompositions(
		array &$added, array &$sources, array $old_compositions, array $new_compositions
	) {
		foreach ($new_compositions as $class_name => $new_composition) {
			$old_composition = isset($old_compositions[$class_name])
				? $old_compositions[$class_name]
				: null;
			if (
				($new_composition != $old_composition)
				|| (
					is_array($old_composition)
					&& (
						array_diff($new_composition, $old_composition)
						|| array_diff($old_composition, $new_composition)
					)
				)
			) {
				$this->moreSourcesAdd($class_name, $sources, $added);
			}
		}
	}

	//---------------------------------------------------------------------- moreSourcesAddNewPlugins
	/**
	 * Compile new plugins
	 *
	 * @param $added      Reflection_Source[]
	 * @param $sources    Reflection_Source[] key is $class_name, or $file_path if no class
	 * @param $old_levels array mixed[string $priority_level][string $plugin_name]
	 * @param $new_levels array mixed[string $priority_level][string $plugin_name]
	 */
	protected function moreSourcesAddNewPlugins(
		array &$added, array &$sources, array $old_levels, array $new_levels
	) {
		foreach ($new_levels as $level => $new_plugins) {
			foreach ($new_plugins as $class_name => $new_plugin) {
				if (!isset($old_levels[$level][$class_name])) {
					$this->moreSourcesAdd($class_name, $sources, $added);
				}
			}
		}
	}

	//------------------------------------------------------------- moreSourcesAddRemovedCompositions
	/**
	 * Compositions in builder.php that were removed : compile composite class
	 *
	 * @param $added            Reflection_Source[]
	 * @param $sources          Reflection_Source[] key is $class_name, or $file_path if no class
	 * @param $old_compositions array string[string $composite_class_name][integer]
	 * @param $new_compositions array string[string $composite_class_name][integer]
	 */
	protected function moreSourcesAddRemovedCompositions(
		array &$added, array &$sources, array $old_compositions, array $new_compositions
	) {
		foreach ($old_compositions as $class_name => $old_composition) {
			if (!isset($new_compositions[$class_name])) {
				$this->moreSourcesAdd($class_name, $sources, $added);
			}
		}
	}

	//------------------------------------------------------------------ moreSourcesAddRemovedPlugins
	/**
	 * Compile removed plugins
	 *
	 * @param $added      Reflection_Source[]
	 * @param $sources    Reflection_Source[] key is $class_name, or $file_path if no class
	 * @param $old_levels array mixed[string $priority_level][string $plugin_name]
	 * @param $new_levels array mixed[string $priority_level][string $plugin_name]
	 */
	protected function moreSourcesAddRemovedPlugins(
		array &$added, array &$sources, array $old_levels, array $new_levels
	) {
		foreach ($old_levels as $level => $old_plugins) {
			foreach ($old_plugins as $class_name => $old_plugin) {
				if (!isset($new_levels[$level][$class_name])) {
					$this->moreSourcesAdd($class_name, $sources, $added);
				}
			}
		}
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * @param $sources Reflection_Source[] key is $class_name, or $file_path if no class
	 * @return Reflection_Source[] added sources list. key is the name of the class ?: the file path
	 */
	public function moreSourcesToCompile(array &$sources)
	{
		$added = [];
		if ($this->hasConfigurationFile($sources)) {
			$this->moreSourcesToCompileReload($added, $sources);
		}
		$this->moreSourcesAddComposites($added, $sources);
		$this->moreSourcesAddChildren($added, $sources);
		return $added;
	}

	//-------------------------------------------------------------------- moreSourcesToCompileReload
	/**
	 * When traits / interfaces are added / removed into builder.php : compile the composite class
	 *
	 * @param $added   Reflection_Source[]
	 * @param $sources Reflection_Source[] key is $class_name, or $file_path if no class
	 */
	protected function moreSourcesToCompileReload(array &$added, array &$sources)
	{
		// save compositions before changes
		$old_compositions = Builder::current()->getCompositions();
		$old_levels       = Session::current()->plugins->getAll(true);
		// apply changes and get new compositions
		if (isset(Main::$current)) {
			Main::$current->resetSession();
		}
		$new_compositions = Builder::current()->getCompositions();
		$new_levels       = Session::current()->plugins->getAll(true);
		// add classes where builder composition changed
		$this->moreSourcesAddModifiedOrNewCompositions(
			$added, $sources, $old_compositions, $new_compositions
		);
		$this->moreSourcesAddRemovedCompositions(
			$added, $sources, $old_compositions, $new_compositions
		);
		// add classes of globally added/removed plugins
		$this->moreSourcesAddNewPlugins($added, $sources, $old_levels, $new_levels);
		$this->moreSourcesAddRemovedPlugins($added, $sources, $old_levels, $new_levels);
	}

	//----------------------------------------------------------------------------- setMainController
	/**
	 * @param $main_controller Main
	 */
	public function setMainController(Main $main_controller)
	{
		$this->main_controller = $main_controller;
	}

}
