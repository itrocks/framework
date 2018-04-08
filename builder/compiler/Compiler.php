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

	//-------------------------------------------------------------------------------- moreSourcesAdd
	/**
	 * @param $class_name string
	 * @param $sources    Reflection_Source[]
	 * @param $added      Reflection_Source[]
	 */
	private function moreSourcesAdd($class_name, array &$sources, array &$added)
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

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * @param $sources Reflection_Source[] Key is the file path
	 * @return Reflection_Source[] added sources list. key is the name of the class ?: the file path
	 */
	public function moreSourcesToCompile(array &$sources)
	{
		$added = [];
		foreach ($sources as $file_path => $source) {
			if (
				(strpos($file_path, SL) !== false)
				&& ctype_lower(substr(rLastParse($file_path, SL), 0, 1))
			) {
				$reload = true;
				break;
			}
		}
		if (isset($reload)) {
			$old_compositions = Builder::current()->getCompositions();
			$old_levels       = Session::current()->plugins->getAll(true);
			if (isset(Main::$current)) {
				Main::$current->resetSession();
			}
			$new_compositions = Builder::current()->getCompositions();
			$new_levels       = Session::current()->plugins->getAll(true);
			// add classes where builder composition changed
			foreach ($old_compositions as $class_name => $old_composition) {
				if (
					!isset($new_compositions[$class_name])
					|| ($old_composition != $new_compositions[$class_name])
					|| (is_array($old_composition) && (
						array_diff($old_composition, $new_compositions[$class_name])
						|| array_diff($new_compositions[$class_name], $old_composition)
					))
				) {
					$this->moreSourcesAdd($class_name, $sources, $added);
				}
			}
			foreach ($new_compositions as $class_name => $new_composition) {
				if (!isset($old_compositions[$class_name])) {
					$this->moreSourcesAdd($class_name, $sources, $added);
				}
			}
			// add classes of globally added/removed plugins
			foreach ($old_levels as $level => $old_plugins) {
				foreach ($old_plugins as $class_name => $old_plugin) {
					if (!isset($new_levels[$level][$class_name])) {
						$this->moreSourcesAdd($class_name, $sources, $added);
					}
				}
			}
			foreach ($new_levels as $level => $new_plugins) {
				foreach ($new_plugins as $class_name => $new_plugin) {
					if (!isset($old_levels[$level][$class_name])) {
						$this->moreSourcesAdd($class_name, $sources, $added);
					}
				}
			}
		}
		return $added;
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
