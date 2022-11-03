<?php
namespace ITRocks\Framework\PHP\Compiler;

use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\Tests\Test;

/**
 * Compiler path, file names and class names tests
 */
class Paths_Test extends Test
{

	//--------------------------------------------------------------------------------- classProvider
	/**
	 * Provides test data for testClassToPathToClass().
	 *
	 * @return array string[][] each string[] is a collection of possible full names for a class
	 */
	public function classProvider() : array
	{
		// Each data set is composed of [class_name, expected_result].
		return [
			[
				'ITRocks\Framework\PHP\Compiler',
				'Itrocks\Framework\Php\Compiler',
			],
			[
				'ITRocks\Framework\PHP\Compiler\Logger',
				'Itrocks\Framework\Php\Compiler\Logger',
			],
		];
	}

	//---------------------------------------------------------------------------------- fileProvider
	/**
	 * Provides test data for testSourceFileToPathToSourceFile().
	 *
	 * @return array string[][] each string[] is a collection of possible full names for a class
	 */
	public function fileProvider() : array
	{
		// Each data set is composed of [source_file, expected_result].
		return [
			[
				'ITRocks/framework/PHP/Compiler.php',
				'itrocks/framework/php/compiler/Compiler.php',
			],
			[
				'ITRocks/framework/PHP/compiler/Logger.php',
				'itrocks/framework/php/compiler/Logger.php',
			],
		];
	}

	//------------------------------------------------------------------------ testClassToPathToClass
	/**
	 * Test that given a class_name, by transforming to compiled file name then reverse,
	 * we get back same class name !
	 *
	 * @dataProvider classProvider
	 * @param $class_name string The test data
	 * @param $expected   string Expected result for given data
	 */
	public function testClassToPathToClass(string $class_name, string $expected)
	{
		$actual = Compiler::cacheFileNameToClass(
			basename(Compiler::classToCacheFilePath($class_name))
		);
		static::assertEquals($expected, $actual);
	}

	//-------------------------------------------------------------- testSourceFileToPathToSourceFile
	/**
	 * Test that given a source file, by transforming to compiled file name then reverse,
	 * we get back same source file name !
	 *
	 * @dataProvider fileProvider
	 * @param $source_file string The given source file
	 * @param $expected    string The expected result for the given source file
	 */
	public function testSourceFileToPathToSourceFile(string $source_file, string $expected)
	{
		$actual = Compiler::cacheFileNameToSourceFile(
			Compiler::sourceFileToCacheFileName($source_file)
		);
		static::assertEquals($expected, $actual);
	}

}
