<?php
namespace SAF\Framework\Builder;

use SAF\Framework\Application;
use SAF\Framework\Builder;
use SAF\Framework\Class_Builder;
use SAF\Framework\Files;
use SAF\Framework\ICompiler;
use SAF\Framework\Php_Compiler;
use SAF\Framework\Php_Source;
use SAF\Framework\Session;

/**
 * Built classes compiler
 */
class Compiler implements ICompiler
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source Php_Source
	 * @return string[]
	 */
	public function compile(Php_Source $source)
	{
		$compiled = false;
		$cache_dir = Application::current()->getCacheDir();
		$recurse = [];
		foreach ($source->getClasses() as $class) {
			$replacement = Builder::current()->getComposition($class->name);
			if ($replacement !== $class->name) {
				foreach (Class_Builder::build($class->name, $replacement, true) as $built_name => $source) {
					$source = '<?php' . LF . $source;
					$path = array_slice(explode(BS, $built_name), 2);
					$file_name = array_pop($path) . '.php';
					$path = $cache_dir . SL . strtolower(join(SL, $path));
					Files::mkdir($path);
					script_put_contents($path . SL . $file_name, $source);
					$recurse[] = $path . SL . $file_name;
					$compiled = true;
				}
			}
		}
		if ($recurse) {
			/** @var $compiler Php_Compiler */
			$compiler = Session::current()->plugins->get(Php_Compiler::class);
			$compiler->compileFiles($recurse);
		}
		return $compiled;
	}

	//---------------------------------------------------------------------------- moreFilesToCompile
	/**
	 * Extends the list of files to compile
	 *
	 * @param $files Php_Source[] Key is the file path
	 * @return boolean true if files were added
	 */
	public function moreFilesToCompile(&$files)
	{
		foreach (array_keys($files) as $file_path) {
			if (!strpos($file_path, SL)) {
				$configuration =
			}
		}
		return false;
	}

}
