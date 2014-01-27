<?php
namespace SAF\Tests\Tests {

use AOPT\Advices;
use AOPT\Business;
use AOPT\Child_Business;
use AOPT\Trait_Business;
use AOPT\Trait_Business_Class;
use AOPT\Trait_Child_Business_Class;
use SAF\Framework\Aop;
use SAF\Framework\Unit_Tests\Unit_Test;

/**
 * Aop unit testing
 */
class Aop_Test extends Unit_Test
{

	//--------------------------------------------- testAddBeforeMethodCallGetterChangesPropertyValue
	/**
	 * Simple advice : replace a property value before calling its getter
	 */
	public function testAddBeforeMethodCallGetterChangesPropertyValue()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'getterA'),
			array('AOPT\Advices', 'adviceChangeProperty')
		);
		$this->assume(__METHOD__, (new Business())->getterA(), "A.acp/advised");
	}

	//--------------------------------------------- testAddBeforeMethodCallGetterChangesReturnedValue
	/**
	 * A "before" advice that cancels standard call and returns a forced value
	 */
	public function testAddBeforeMethodCallGetterChangesReturnedValue()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'getterB'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$this->assume(__METHOD__, (new Business())->getterB(), "arv/advised value");
	}

	//-------------------------------------------------- testAddBeforeMethodCallGetterParentJoinpoint
	/**
	 * An advice on a getter that is overloaded : will be called only when calling the parent method
	 */
	public function testAddBeforeMethodCallGetterParentJoinpoint()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'getterC'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$this->assume(__METHOD__ . ".parent", (new Business())->getterC(), "arv/advised value");
		$this->assume(__METHOD__ . ".child", (new Child_Business())->getterC(), "ChildC.arv/advised value");
	}

	//--------------------------------------------------- testAddBeforeMethodCallGetterChildJoinpoint
	/**
	 * An advice on an overloaded getter : will be called only when calling the child method,
	 * not for parent
	 */
	public function testAddBeforeMethodCallGetterChildJoinpoint()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Child_Business', 'getterD'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$this->assume(__METHOD__ . ".parent", (new Business())->getterD(), "D.value");
		$this->assume(__METHOD__ . ".child", (new Child_Business())->getterD(), "arv/advised value");
	}

	//---------------------------------------------------------------- testAddBeforeMethodCallOnTrait
	/**
	 * An advice on a trait : must be called on each classes using the trait, but not on children
	 */
	public function testAddBeforeMethodCallOnTrait()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Trait_Business', 'methodE'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$this->assume(__METHOD__ . ".parent", (new Trait_Business_Class())->methodE(), "arv/advised value");
		$this->assume(__METHOD__ . ".child", (new Trait_Child_Business_Class())->methodE(), "over.E/value");
	}

	//----------------------------------------------------------- testAddBeforeMethodCallObjectAdvice
	/**
	 * An object advice
	 */
	public function testAddBeforeMethodCallObjectAdvice()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'methodF'),
			array(new Advices("advice"), 'adviceObject')
		);
		$this->assume(__METHOD__, (new Business())->methodF(), "ao/advised advice/value");
	}

	//------------------------------------------------------------- testAddOnPropertyReadStaticGetter
	public function testAddOnPropertyReadStaticGetter()
	{
		Aop::addOnPropertyRead(array('AOPT\Business', 'property2'), array('AOPT\Business', 'getProperty2'));
		$object = new Business();
		$this->assume(__METHOD__, $object->property2, "get2.value2");
	}

}

}
namespace AOPT {

//========================================================================================= Advices
/**
 * A class containing a lot of advices
 */
class Advices
{

	public $advice_property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $arg string
	 */
	public function __construct($arg)
	{
		$this->advice_property = $arg;
	}

	//-------------------------------------------------------------------------- adviceChangeProperty
	/**
	 * This advice changes the business object property value
	 *
	 * @param $object Business
	 */
	public static function adviceChangeProperty($object)
	{
		$object->property = "acp/advised";
	}

	//--------------------------------------------------------------------------- adviceReturnedValue
	/**
	 * This advice changes the business object property value
	 *
	 * @param $object Business
	 * @return string
	 */
	public static function adviceReturnedValue($object)
	{
		return "arv/advised " . $object->property;
	}

	//---------------------------------------------------------------------------------- adviceObject
	/**
	 * This advice is called on an Advices object
	 *
	 * @param $object
	 * @return string
	 */
	public function adviceObject($object)
	{
		return "ao/advised " . $this->advice_property . "/" . $object->property;
	}

}

//======================================================================================== Business
/** A business class example */
class Business
{
	public $property = "value";
	public $property2 = "value2";
	/** @return string */ public function getterA() { return "A." . $this->property; }
	/** @return string */ public function getterB() { return "B." . $this->property; }
	/** @return string */ public function getterC() { return "C." . $this->property; }
	/** @return string */ public function getterD() { return "D." . $this->property; }
	/** @return string */ public function methodF() { return "F."; }
	/** @return string */ public static function getProperty2($value) { return "get2." . $value; }
}

//================================================================================== Child_Business
/** Child business class example */
class Child_Business extends Business
{
	/** @return string */ public function getterC() { return "ChildC." . parent::getterC(); }
	/** @return string */ public function getterD() { return "ChildD." . parent::getterC(); }
}

//================================================================================== Trait_Business
/** Business trait example */
trait Trait_Business
{
	/** @return string */ public function methodE() { return "E/value"; }
}

//============================================================================ Trait_Business_Class
/** A business class that use a business trait */
class Trait_Business_Class extends Child_Business
{
	use Trait_Business;
}

//====================================================================== Trait_Child_Business_Class
/** A business class child of a class that use a business trait */
class Trait_Child_Business_Class extends Trait_Business_Class
{
	//------------------------------------------------------------------------------------ joinPointE
	/** @return string */ public function methodE() { return "over.E/value"; }

}

}
