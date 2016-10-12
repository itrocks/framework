<?php
namespace SAF\Framework\Reflection\Annotation\Tests;

use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tests\Test;

/**
 * Doc-comment annotations tests
 */
class Doc_Comment_Annotations_Tests extends Test
{

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * An empty
	 */
	public function beforeWrite()
	{
	}

	//------------------------------------------------------------------------ testSameInterfaceTwice
	/**
	 * Test case :
	 * A class implements an interface and inherits a class that implements the same interface
	 * Will the annotation coming from the annotation be implemented once or twice ? Should once.
	 */
	public function testSameInterfaceTwice()
	{
		$namespace = 'namespace ' . __NAMESPACE__ . ';' . LF . LF;
		eval($namespace . <<<EOT
/**
 * @before_write Doc_Comment_Annotations_Tests::beforeWrite
 */
interface Test_Interface
{
}
EOT
		);
		eval($namespace . 'class Parent_Class implements Test_Interface {}');
		eval($namespace . 'class Child_Class extends Parent_Class {}');
		$class = (new Reflection_Class(__NAMESPACE__ . BS . 'Child_Class'));
		$annotations = $class->getAnnotations('before_write');
		$assume = [new Method_Annotation(BS . __CLASS__ . '::beforeWrite', $class, 'before_write')];
		$this->assume(__METHOD__, $annotations, $assume);
	}

}
