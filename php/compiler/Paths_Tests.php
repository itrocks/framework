<?php
namespace ITRocks\Framework\PHP\Compiler;

use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\Tests\Test;

/**
 * Compiler path, file names  and class names tests
 */
class Paths_Tests extends Test
{

	//------------------------------------------------------------------------ testClassToPathToClass
	/**
	 * Test that given a class_name, by transforming to compiled file name then reverse,
	 * we get back same class name !
	 */
	public function testClassToPathToClass()
	{
		$i = 0;

		$assume = Compiler::class;
		$check  = Compiler::pathToClass(Compiler::classToPath($assume));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);

		$assume = Logger::class;
		$check  = Compiler::pathToClass(Compiler::classToPath($assume));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);
	}

	//-------------------------------------------------------------- testSourceFileToPathToSourceFile
	/**
	 * Test that given a source file, by transforming to compiled file name then reverse,
	 * we get back same source file name !
	 */
	public function testSourceFileToPathToSourceFile()
	{
		$i = 0;

		$assume = 'itrocks/framework/php/Compiler.php';
		$check  = Compiler::pathToSourceFile(Compiler::sourceFileToPath($assume));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);

		$assume = 'itrocks/framework/php/compiler/Logger.php';
		$check  = Compiler::pathToSourceFile(Compiler::sourceFileToPath($assume));
		$this->assume(__METHOD__ . '_' . ++$i, $check, $assume);
	}

}
