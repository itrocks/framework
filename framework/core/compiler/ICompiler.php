<?php
namespace SAF\Framework;

/**
 * All Php compiler files should use this interface
 */
interface ICompiler
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source Php_Source
	 * @return Php_Source[]|boolean true if compilation process did something
	 *         may be a list of files if new files will need to be compiled due to this compilation
	 */
	public function compile(Php_Source $source);

	//---------------------------------------------------------------------------- moreFilesToCompile
	/**
	 * Extends the list of files to compile
	 *
	 * @param $files Php_Source[] Key is the file path
	 * @return boolean true if files were added
	 */
	public function moreFilesToCompile(&$files);

}
