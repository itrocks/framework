<?php
namespace ITRocks\Framework\PHP\Compiler;

use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\Tests\Test;

/**
 * Compiler path, file names  and class names tests
 *
 * TODO HIGH rename willTest* to test* when code is ready
 */
class Paths_Tests extends Test
{

	//-------------------------------------------------------------------- willTestClassToPathToClass
	/**
	 * Test that given a class_name, by transforming to compiled file name then reverse,
	 * we get back same class name !
	 */
	public function willTestClassToPathToClass()
	{
		$i = 0;

		$assume = Compiler::class;
		$check  = Compiler::cacheFileNameToClass(basename(Compiler::classToCacheFilePath($assume)));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);

		$assume = Logger::class;
		$check  = Compiler::cacheFileNameToClass(basename(Compiler::classToCacheFilePath($assume)));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);
	}

	//---------------------------------------------------------- willTestSourceFileToPathToSourceFile
	/**
	 * Test that given a source file, by transforming to compiled file name then reverse,
	 * we get back same source file name !
	 */
	public function willTestSourceFileToPathToSourceFile()
	{
		$i = 0;

		$assume = 'itrocks/framework/php/Compiler.php';
		$check  = Compiler::cacheFileNameToSourceFile(Compiler::sourceFileToCacheFileName($assume));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);

		$assume = 'itrocks/framework/php/compiler/Logger.php';
		$check  = Compiler::cacheFileNameToSourceFile(Compiler::sourceFileToCacheFileName($assume));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);
	}

}
