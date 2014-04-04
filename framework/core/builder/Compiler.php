<?php
namespace SAF\Framework\Builder;

use SAF\Framework\Builder;
use SAF\Framework\Class_Builder;
use SAF\Framework\ICompiler;
use SAF\Framework\Main_Controller;
use SAF\Framework\Needs_Main_Controller;
use SAF\Framework\Php_Compiler;
use SAF\Framework\Php_Source;

/**
 * Built classes compiler
 */
class Compiler implements ICompiler, Needs_Main_Controller
{

	//------------------------------------------------------------------------------ $main_controller
	/**
	 * @var $main_Controller Main_Controller
	 */
	private $main_controller;

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source   Php_Source
	 * @param $compiler Php_Compiler
	 * @return boolean
	 */
	public function compile(Php_Source $source, Php_Compiler $compiler = null)
	{
		$compiled = false;
		foreach ($source->getClasses() as $class) {
			$replacement = Builder::current()->getComposition($class->name);
			if (is_array($replacement)) {
				foreach (Class_Builder::build($class->name, $replacement, true) as $source) {
					$compiler->addSource((new Php_Source())->setSource('<?php' . LF . $source));
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
	 * @param $files Php_Source[] Key is the file path
	 * @return boolean true if files were added
	 */
	public function moreSourcesToCompile(&$files)
	{
		foreach (array_keys($files) as $file_path) {
			if (!strpos($file_path, SL)) {

				// get builder classes before compilation
				$old_compositions = Builder::current()->getCompositions();

				// if any of the scripts files in root has been changed : reset session
				$this->main_controller->resetSession();

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
	 * @param $main_controller Main_Controller
	 */
	public function setMainController(Main_Controller $main_controller)
	{
		$this->main_controller = $main_controller;
	}

}
