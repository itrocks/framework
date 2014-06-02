<?php
namespace SAF\Framework\Builder;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Needs_Main;
use SAF\Framework\Dao;
use SAF\Framework\Mapper\Search_Object;
use SAF\Framework\PHP;
use SAF\Framework\PHP\Dependency;
use SAF\Framework\PHP\ICompiler;
use SAF\Framework\PHP\Reflection_Source;

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
	 * @param $source   Reflection_Source
	 * @param $compiler PHP\Compiler
	 * @return boolean
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null)
	{
		Builder::current()->build = false;
		$compiled = false;
		foreach ($source->getClasses() as $class) {

			// create Built class
			$replacement = Builder::current()->getComposition($class->name);
			if (is_array($replacement)) {
				foreach (Class_Builder::build($class->name, $replacement, true) as $source) {
					$compiler->addSource((new Reflection_Source())->setSource('<?php' . LF . $source));
					$compiled = true;
				}
			}

			// replace extends with the built replacement class
			$parent_class_name = $class->getParentName();
			$replacement_class_name = Builder::className($parent_class_name);
			if ($replacement_class_name !== $parent_class_name) {
				$buffer = $source->getSource();
				$buffer = str_replace(
					'extends ' . $parent_class_name,
					'extends ' . $replacement_class_name,
					$buffer
				);
				$source->setSource($buffer);
			}

		}
		Builder::current()->build = true;
		return $compiled;
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * Extends the list of files to compile
	 *
	 * @param $sources Reflection_Source[] Key is the file path
	 * @return Reflection_Source[] added sources list
	 */
	public function moreSourcesToCompile(&$sources)
	{
		$added = [];

		// we will search all extends dependencies
		/** @var $dependency Dependency */
		$dependency_search = Search_Object::create(Dependency::class);
		$dependency_search->type = Dependency::T_EXTENDS;

		foreach ($sources as $file_path => $source) {

			if (!strpos($file_path, SL)) {
				// get builder classes before compilation
				$old_compositions = Builder::current()->getCompositions();

				foreach (Builder::current()->getCompositions() as $class_name => $replacement) {
					if (
						!isset($old_compositions[$class_name])
						|| ($old_compositions[$class_name] !== $replacement)
					) {
						// TODO check if this work (not very sure... does it ?)
						if (!isset($sources[$dependency->file_name])) {
							$added[$dependency->file_name] = new Reflection_Source($dependency->file_name);
						}
					}
				}
			}

			foreach ($source->getClasses() as $class) {
				if (!Builder::isBuilt($class->name)) {
					// add all classes that extend source classes
					$dependency_search->dependency_name = $class->name;
					foreach (Dao::search($dependency_search) as $dependency) {
						if (
							!isset($sources[$dependency->file_name])
							&& !Builder::isBuilt($dependency->class_name)
						) {
							$added[$dependency->file_name] = new Reflection_Source($dependency->file_name);
						}
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
