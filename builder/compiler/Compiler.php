<?php
namespace ITRocks\Framework\Builder;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Needs_Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP;
use ITRocks\Framework\PHP\Compiler\More_Sources;
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
				foreach ((new Class_Builder)->build($class->name, $replacement, true) as $built_source) {
					$compiler->addSource((new Reflection_Source)->setSource('<?php' . LF . $built_source));
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
	 * @param $class_name   string
	 * @param $more_sources More_Sources
	 */
	protected function moreSourcesAdd($class_name, More_Sources $more_sources)
	{
		$dependency = Dao::searchOne(
			['class_name' => $class_name, 'dependency_name' => $class_name], Dependency::class
		);
		if (!isset($more_sources->sources[$dependency->file_name])) {
			$source = Reflection_Source::ofFile($dependency->file_name, $class_name);
			$more_sources->add($source, $class_name, null, true);
		}
	}

	//------------------------------------------------------------------------ moreSourcesAddChildren
	/**
	 * @param $more_sources More_Sources
	 */
	protected function moreSourcesAddChildren(More_Sources $more_sources)
	{
		$add_to = $more_sources->added;
		while ($add_to) {
			$next_add_to = [];
			foreach ($add_to as $source) {
				$dependencies = Dao::search(
					[
						'dependency_name' => $source->getFirstClassName(),
						'type' => [Dependency::T_EXTENDS, Dependency::T_USE, Dependency::T_IMPLEMENTS]
					],
					Dependency::class
				);
				foreach ($dependencies as $dependency) {
					if (
						!Class_Builder::isBuilt($dependency->class_name)
						&& !isset($more_sources->sources[$dependency->file_name])
						&& !isset($more_sources->sources[$dependency->class_name])
					) {
						$source = Reflection_Source::ofFile($dependency->file_name, $dependency->class_name);
						$more_sources->add($source, $dependency->class_name, null, true);
						$next_add_to[$dependency->class_name] = $source;
					}
				}
			}
			$add_to = $next_add_to;
		}
	}

	//---------------------------------------------------------------------- moreSourcesAddComposites
	/**
	 * When traits / interfaces referenced into builder.php are modified : compile the composite class
	 *
	 * @param $more_sources More_Sources
	 */
	protected function moreSourcesAddComposites(More_Sources $more_sources)
	{
		foreach (Builder::current()->getCompositions() as $built_class_name => $composition) {
			if (is_string($composition)) {
				$composition = [$composition];
			}
			foreach ($composition as $component_class_name) {
				if (
					isset($more_sources->sources[$component_class_name])
					&& !isset($more_sources->sources[$built_class_name])
					&& !isset($more_sources->added[$built_class_name])
				) {
					$this->moreSourcesAdd($built_class_name, $more_sources);
				}
			}
		}
	}

	//------------------------------------------------------- moreSourcesAddModifiedOrNewCompositions
	/**
	 * Compositions in builder.php that were added or modified : compile composite class
	 *
	 * @param $more_sources     More_Sources
	 * @param $old_compositions array string[string $composite_class_name][integer]
	 * @param $new_compositions array string[string $composite_class_name][integer]
	 */
	protected function moreSourcesAddModifiedOrNewCompositions(
		More_Sources $more_sources, array $old_compositions, array $new_compositions
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
				$this->moreSourcesAdd($class_name, $more_sources);
			}
		}
	}

	//---------------------------------------------------------------------- moreSourcesAddNewPlugins
	/**
	 * Compile new plugins
	 *
	 * @param $more_sources More_Sources
	 * @param $old_levels   array mixed[string $priority_level][string $plugin_name]
	 * @param $new_levels   array mixed[string $priority_level][string $plugin_name]
	 */
	protected function moreSourcesAddNewPlugins(
		More_Sources $more_sources, array $old_levels, array $new_levels
	) {
		foreach ($new_levels as $level => $new_plugins) {
			foreach ($new_plugins as $class_name => $new_plugin) {
				if (!isset($old_levels[$level][$class_name])) {
					$this->moreSourcesAdd($class_name, $more_sources);
				}
			}
		}
	}

	//------------------------------------------------------------- moreSourcesAddRemovedCompositions
	/**
	 * Compositions in builder.php that were removed : compile composite class
	 *
	 * @param $more_sources     More_Sources
	 * @param $old_compositions array string[string $composite_class_name][integer]
	 * @param $new_compositions array string[string $composite_class_name][integer]
	 */
	protected function moreSourcesAddRemovedCompositions(
		More_Sources $more_sources, array $old_compositions, array $new_compositions
	) {
		foreach ($old_compositions as $class_name => $old_composition) {
			if (!isset($new_compositions[$class_name])) {
				$this->moreSourcesAdd($class_name, $more_sources);
			}
		}
	}

	//------------------------------------------------------------------ moreSourcesAddRemovedPlugins
	/**
	 * Compile removed plugins
	 *
	 * @param $more_sources More_Sources
	 * @param $old_levels   array mixed[string $priority_level][string $plugin_name]
	 * @param $new_levels   array mixed[string $priority_level][string $plugin_name]
	 */
	protected function moreSourcesAddRemovedPlugins(
		More_Sources $more_sources, array $old_levels, array $new_levels
	) {
		foreach ($old_levels as $level => $old_plugins) {
			foreach ($old_plugins as $class_name => $old_plugin) {
				if (!isset($new_levels[$level][$class_name])) {
					$this->moreSourcesAdd($class_name, $more_sources);
				}
			}
		}
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * @param $more_sources More_Sources
	 */
	public function moreSourcesToCompile(More_Sources $more_sources)
	{
		if ($this->hasConfigurationFile($more_sources->sources)) {
			$this->moreSourcesToCompileReload($more_sources);
		}
		$this->moreSourcesAddComposites($more_sources);
		$this->moreSourcesAddChildren($more_sources);
	}

	//-------------------------------------------------------------------- moreSourcesToCompileReload
	/**
	 * When traits / interfaces are added / removed into builder.php : compile the composite class
	 *
	 * @param $more_sources More_Sources
	 */
	protected function moreSourcesToCompileReload(More_Sources $more_sources)
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
			$more_sources, $old_compositions, $new_compositions
		);
		$this->moreSourcesAddRemovedCompositions(
			$more_sources, $old_compositions, $new_compositions
		);
		// add classes of globally added/removed plugins
		$this->moreSourcesAddNewPlugins($more_sources, $old_levels, $new_levels);
		$this->moreSourcesAddRemovedPlugins($more_sources, $old_levels, $new_levels);
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
