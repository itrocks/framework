<?php
namespace SAF\Tests\Tests {

use AOPT\Advices;
use AOPT\Business;
use AOPT\Child_Business;
use AOPT\Trait_Business;
use AOPT\Trait_Business_Class;
use AOPT\Trait_Child_Business_Class;
use SAF\AOP;
use SAF\Framework\Unit_Tests\Unit_Test;

/**
 * Aop unit testing
 */
class Aop_Test extends Unit_Test
{

	//---------------------------------------------------------------------- testAddAfterFunctionCall
	/**
	 * An advice after an internal function call which has a default value on its second parameter
	 */
	public function testAddAfterFunctionCall()
	{
		$this->captureStart();
		$handler = Aop::addAfterFunctionCall("chop", array('AOPT\Advices', 'afterChop'));
		$result = chop("Hello ");
		$this->assumeCapture(__METHOD__, "Hello | \t\n\r\0\x0B|Hello");
		$this->assume(__METHOD__, $result, "Hello.AFT.");
		Aop::remove($handler);
	}

	//--------------------------------------------------------------------- testAddAroundFunctionCall
	/**
	 * An advice around an internal function call which has a default value on its second parameter
	 */
	public function testAddAroundFunctionCall()
	{
		$this->captureStart();
		$handler = Aop::addAroundFunctionCall("chop", array('AOPT\Advices', 'aroundChop'));
		$result = chop(" Hello ");
		$this->assumeCapture(__METHOD__, "before= Hello | \t\n\r\0\x0B|after= Hello");
		$this->assume(__METHOD__, $result, "Hello");
		Aop::remove($handler);
	}

	//--------------------------------------------------------------------- testAddBeforeFunctionCall
	/**
	 * An advice before an internal function call which has a default value on its second parameter
	 */
	public function testAddBeforeFunctionCall()
	{
		$this->captureStart();
		$handler = Aop::addBeforeFunctionCall("chop", array('AOPT\Advices', 'beforeChop'));
		$result = chop("Hello ");
		$this->assumeCapture(__METHOD__, "Hello | \t\n\r\0\x0B");
		$this->assume(__METHOD__, $result, " [Hello ]");
		Aop::remove($handler);
	}

	//--------------------------------------------- testAddBeforeMethodCallGetterChangesPropertyValue
	/**
	 * Simple advice : replace a property value before calling its getter
	 */
	public function testAddBeforeMethodCallGetterChangesPropertyValue()
	{
		$this->captureStart();
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'getterA'),
			array('AOPT\Advices', 'adviceChangeProperty')
		);
		$result = (new Business())->getterA();
		$this->assumeCapture(__METHOD__, "adviceChangePropertyStart.adviceChangePropertyEnd.getterA.");
		$this->assume(__METHOD__, $result, "A.acp/advised");
	}

	//--------------------------------------------- testAddBeforeMethodCallGetterChangesReturnedValue
	/**
	 * A "before" advice that cancels standard call and returns a forced value
	 */
	public function testAddBeforeMethodCallGetterChangesReturnedValue()
	{
		$this->captureStart();
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'getterB'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$result = (new Business())->getterB();
		$this->assumeCapture(__METHOD__, "adviceReturnedValueStart.adviceReturnedValueEnd.getterB.");
		$this->assume(__METHOD__, $result, "arv/advised value");
	}

	//-------------------------------------------------- testAddBeforeMethodCallGetterParentJoinpoint
	/**
	 * An advice on a getter that is overloaded : will be called only when calling the parent method
	 */
	public function testAddBeforeMethodCallGetterParentJoinpoint()
	{
		$this->captureStart();
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'getterC'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$parent_result = (new Business())->getterC();
		echo "|";
		$child_result = (new Child_Business())->getterC();
		$this->assumeCapture(__METHOD__,
			"adviceReturnedValueStart.adviceReturnedValueEnd.getterC."
			. "|CBgetterC.adviceReturnedValueStart.adviceReturnedValueEnd.getterC."
		);
		$this->assume(__METHOD__ . ".parent", $parent_result, "arv/advised value");
		$this->assume(__METHOD__ . ".child", $child_result, "ChildC.arv/advised value");
	}

	//--------------------------------------------------- testAddBeforeMethodCallGetterChildJoinpoint
	/**
	 * An advice on an overloaded getter : will be called only when calling the child method,
	 * not for parent
	 */
	public function testAddBeforeMethodCallGetterChildJoinpoint()
	{
		$this->captureStart();
		Aop::addBeforeMethodCall(
			array('AOPT\Child_Business', 'getterD'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$parent_result = (new Business())->getterD();
		echo "|";
		$child_result = (new Child_Business())->getterD();
		$this->assumeCapture(__METHOD__,
			"getterD.|adviceReturnedValueStart.adviceReturnedValueEnd."
			. "CBgetterD.adviceReturnedValueStart.adviceReturnedValueEnd.getterC."
		);
		$this->assume(__METHOD__ . ".parent", $parent_result, "D.value");
		$this->assume(__METHOD__ . ".child", $child_result, "arv/advised value");
	}

	//---------------------------------------------------------------- testAddBeforeMethodCallOnTrait
	/**
	 * An advice on a trait : must be called on each classes using the trait,
	 * but not on children overridden methods
	 */
	public function testAddBeforeMethodCallOnTrait()
	{
		$this->captureStart();
		Aop::addBeforeMethodCall(
			array('AOPT\Trait_Business', 'methodE'),
			array('AOPT\Advices', 'adviceReturnedValue')
		);
		$parent_result = (new Trait_Business_Class())->methodE();
		echo "|";
		$child_result = (new Trait_Child_Business_Class())->methodE();
		$this->assumeCapture(
			__METHOD__, "adviceReturnedValueStart.adviceReturnedValueEnd.TmethodE.|TCmethodE."
		);
		$this->assume(__METHOD__ . ".parent", $parent_result, "arv/advised value");
		$this->assume(__METHOD__ . ".child", $child_result, "over.E/value");
	}

	//----------------------------------------------------------- testAddBeforeMethodCallObjectAdvice
	/**
	 * The advice is a method on an object, instead of a static method of a class
	 */
	public function testAddBeforeMethodCallObjectAdvice()
	{
		$this->captureStart();
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'methodF'),
			array(new Advices("advice"), 'adviceObject')
		);
		$result = (new Business())->methodF();
		$this->assumeCapture(__METHOD__, "adviceObjectStart.adviceObjectEnd.getterF.");
		$this->assume(__METHOD__, $result, "ao/advised advice/value");
	}

	//------------------------------------------------------------------- testAddOnPropertyReadGetter
	public function testAddOnPropertyReadGetter()
	{
		$this->captureStart();
		Aop::addOnPropertyRead(
			array('AOPT\Business', 'property3'), array('AOPT\Business', 'getProperty3')
		);
		$result = (new Business())->property3;
		$this->assumeCapture(__METHOD__, "getProperty3.");
		$this->assume(__METHOD__, $result, "3bis.value3");
	}

	//------------------------------------------------------------- testAddOnPropertyReadStaticGetter
	public function testAddOnPropertyReadStaticGetter()
	{
		$this->captureStart();
		Aop::addOnPropertyRead(
			array('AOPT\Business', 'property2'), array('AOPT\Business', 'getProperty2')
		);
		$result = (new Business())->property2;
		$this->assumeCapture(__METHOD__, "getProperty2.");
		$this->assume(__METHOD__, $result, "get2.value2");
	}

	//------------------------------------------------------------------ testAddOnPropertyWriteSetter
	public function testAddOnPropertyWriteSetter()
	{
		$this->captureStart();
		Aop::addOnPropertyWrite(
			array('AOPT\Business', 'property5'), array('AOPT\Business', 'setProperty5')
		);
		$object = new Business();
		$object->property5 = "value5";
		$this->assumeCapture(__METHOD__, "setProperty5.");
		$this->assume(__METHOD__, $object->property5, "5bis.value5");
	}

	//------------------------------------------------------------ testAddOnPropertyWriteStaticSetter
	public function testAddOnPropertyWriteStaticSetter()
	{
		$this->captureStart();
		Aop::addOnPropertyWrite(
			array('AOPT\Business', 'property4'), array('AOPT\Business', 'setProperty4')
		);
		$object = new Business();
		$object->property4 = "value4";
		$this->assumeCapture(__METHOD__, "setProperty4.");
		$this->assume(__METHOD__, $object->property4, "set4.value4");
	}

}

}

//############################################################################################ AOPT
namespace AOPT {

use SAF\Framework\Around_Function_Joinpoint;

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
		echo "adviceChangePropertyStart.";
		$object->property = "acp/advised";
		echo "adviceChangePropertyEnd.";
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
		echo "adviceReturnedValueStart.";
		$result = "arv/advised " . $object->property;
		echo "adviceReturnedValueEnd.";
		return $result;
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
		echo "adviceObjectStart.";
		$result = "ao/advised " . $this->advice_property . "/" . $object->property;
		echo "adviceObjectEnd.";
		return $result;
	}

	//------------------------------------------------------------------------------------- afterChop
	/**
	 * @param $str            string
	 * @param $character_mask string
	 * @param $result         string
	 * @return string
	 */
	public static function afterChop($str, $character_mask, $result)
	{
		echo "$str|$character_mask|$result";
		return $result . ".AFT.";
	}

	//------------------------------------------------------------------------------------ beforeChop
	/**
	 * @param $str            string
	 * @param $character_mask string
	 * @param $joinpoint      Around_Function_Joinpoint
	 * @return string
	 */
	public static function aroundChop($str, $character_mask, Around_Function_Joinpoint $joinpoint)
	{
		echo "before=$str|$character_mask";
		$result = $joinpoint->process($str, $character_mask);
		echo "|after=$result";
		return trim($result);
	}

	//------------------------------------------------------------------------------------ beforeChop
	/**
	 * @param $str            string
	 * @param $character_mask string
	 */
	public static function beforeChop(&$str, $character_mask)
	{
		echo "$str|$character_mask";
		$str = " [" . $str . "] ";
	}

}

//======================================================================================== Business
/** A business class example */
class Business
{
	public $property = "value";
	public $property2 = "value2";
	public $property3 = "value3";
	public $property3bis = "3bis";
	public $property4;
	public $property5;
	public $property5bis = "5bis";

	/** */ public function getterA() { echo "getterA."; return "A." . $this->property; }
	/** */ public function getterB() { echo "getterB."; return "B." . $this->property; }
	/** */ public function getterC() { echo "getterC."; return "C." . $this->property; }
	/** */ public function getterD() { echo "getterD."; return "D." . $this->property; }
	/** */ public function methodF() { echo "getterF."; return "F."; }
	/** */ public static function getProperty2($value) { echo "getProperty2."; return "get2." . $value; }
	/** */ public function getProperty3($value) { echo "getProperty3."; return $this->property3bis . "." . $value; }
	/** */ public static function setProperty4($value) { echo "setProperty4."; return "set4." . $value; }
	/** */ public function setProperty5($value) { echo "setProperty5."; return $this->property5bis . "." . $value; }
}

//================================================================================== Child_Business
/** Child business class example */
class Child_Business extends Business
{
	/** */ public function getterC() { echo "CBgetterC."; return "ChildC." . parent::getterC(); }
	/** */ public function getterD() { echo "CBgetterD.";return "ChildD." . parent::getterC(); }
}

//================================================================================== Trait_Business
/** Business trait example */
trait Trait_Business
{
	/** */ public function methodE() { echo "TmethodE."; return "E/value"; }
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
	/** */ public function methodE() { echo "TCmethodE."; return "over.E/value"; }

}

}
