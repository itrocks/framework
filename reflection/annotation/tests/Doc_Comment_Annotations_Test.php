<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;

/**
 * Doc-comment annotations tests
 */
class Doc_Comment_Annotations_Test extends Test
{

	//------------------------------------------------------------------------ testSameInterfaceTwice
	/**
	 * Test case :
	 * A class implements an interface and inherits a class that implements the same interface
	 * Will the annotation coming from the annotation be implemented once or twice ? Should once.
	 */
	public function testSameInterfaceTwice() : void
	{
		$namespace = 'namespace ' . __NAMESPACE__ . ' {' . LF . LF;
		if (!interface_exists(__NAMESPACE__ . BS . 'Test_Interface', false)) {
			eval($namespace . <<<EOT
/**
 * @before_write Doc_Comment_Annotations_Test::beforeWrite
 */
interface Test_Interface
{
}

}
EOT
			);
			eval($namespace . 'class Parent_Class implements Test_Interface {} }');
			eval($namespace . 'class Child_Class extends Parent_Class {} }');
		}
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$class  = (new Reflection_Class(__NAMESPACE__ . BS . 'Child_Class'));
		$assume = [new Method_Annotation(BS . __CLASS__ . '::beforeWrite', $class, 'before_write')];
		$annotations = $class->getAnnotations('before_write');
		self::assertEquals($assume, $annotations);
	}

}
