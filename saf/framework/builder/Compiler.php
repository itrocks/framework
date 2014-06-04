<?php
namespace SAF\Framework\Builder;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Needs_Main;
use SAF\Framework\Dao;
use SAF\Framework\PHP;
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
	 * Compile built classes
	 *
	 * @param $source   Reflection_Source
	 * @param $compiler PHP\Compiler
	 * @return boolean
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null)
	{
		$builder = Builder::current();
		$builder->build = false;
		$compiled = false;
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
		return $compiled;
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * @param $sources Reflection_Source[] Key is the file path
	 * @return Reflection_Source[] added sources list
	 */
	public function moreSourcesToCompile(&$sources)
	{
		$added = [];
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
						/*
						if (!isset($sources[$dependency->file_name])) {
							$added[$dependency->file_name] = new Reflection_Source($dependency->file_name);
						}
						*/
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
