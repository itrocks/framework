<?php
namespace SAF\Framework\Builder;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Needs_Main;
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
	 * @param $source   Reflection_Source
	 * @param $compiler PHP\Compiler
	 * @return boolean
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null)
	{
		$compiled = false;
		foreach ($source->getClasses() as $class) {
			$replacement = Builder::current()->getComposition($class->name);
			if (is_array($replacement)) {
				foreach (Class_Builder::build($class->name, $replacement, true) as $source) {
					$compiler->addSource((new Reflection_Source())->setSource('<?php' . LF . $source));
					$compiled = true;
				}
			}
		}
		return $compiled;
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * Extends the list of files to compile
	 *
	 * @param $files Reflection_Source[] Key is the file path
	 * @return boolean true if files were added
	 */
	public function moreSourcesToCompile(&$files)
	{
		foreach (array_keys($files) as $file_path) {
			if (!strpos($file_path, SL)) {

				// get builder classes before compilation
				$old_compositions = Builder::current()->getCompositions();

				foreach (Builder::current()->getCompositions() as $class_name => $replacement) {
					if (
						!isset($old_compositions[$class_name])
						|| ($old_compositions[$class_name] !== $replacement)
					) {
						// TODO if not already file then add file
					}
				}

				break;
			}
		}
		return false;
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
