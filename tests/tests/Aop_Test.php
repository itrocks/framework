<?php
namespace SAF\Tests\Tests {

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

	//---------------------------------------------------------------------- testAddBeforeMethodCallA
	/**
	 * Simple advice : replace a property value before calling the method who will return it
	 */
	public function testAddBeforeMethodCallA()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'joinPointA'),
			array('AOPT\Advices', 'adviceA')
		);
		$this->assume(__METHOD__, (new Business())->joinPointA(), "A.a/advised");
	}

	//---------------------------------------------------------------------- testAddBeforeMethodCallB
	/**
	 * An advice that force stopping of main call and directly return a modified value
	 */
	public function testAddBeforeMethodCallB()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'joinPointB'),
			array('AOPT\Advices', 'adviceB')
		);
		$this->assume(__METHOD__, (new Business())->joinPointB(), "b/advised value");
	}

	//---------------------------------------------------------------------- testAddBeforeMethodCallC
	/**
	 * An advice on a method that is overloaded : will be called only when calling the parent method
	 */
	public function testAddBeforeMethodCallC()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Business', 'joinPointC'),
			array('AOPT\Advices', 'adviceC')
		);
		$this->assume(__METHOD__, (new Child_Business())->joinPointC(), "Child.C.c/advised value");
	}

	//---------------------------------------------------------------------- testAddBeforeMethodCallC
	/**
	 * An advice on a trait : must be called on each classes using the trait
	 */
	public function testAddBeforeMethodCallD()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Trait_Business', 'joinPointD'),
			array('AOPT\Advices', 'adviceD')
		);
		$this->assume(__METHOD__, (new Trait_Business_Class())->joinPointD(), "d/advised value");
	}

	//---------------------------------------------------------------------- testAddBeforeMethodCallE
	/**
	 * The advice on a trait should not be called on a child class of a class that uses the trait
	 */
	public function testAddBeforeMethodCallE()
	{
		Aop::addBeforeMethodCall(
			array('AOPT\Trait_Business', 'joinPointE'),
			array('AOPT\Advices', 'adviceE')
		);
		$this->assume(__METHOD__, (new Trait_Child_Business_Class())->joinPointE(), "overloaded.e/value");
	}

}

}
namespace AOPT {

//========================================================================================= Advices
/**
 * A class containing a lot of advices
 */
abstract class Advices
{

	//---------------------------------------------------------------------------------------- advice
	/**
	 * This advice changes the business object property value
	 *
	 * @param $object Business
	 */
	public static function adviceA($object)
	{
		$object->property = "a/advised";
	}

	//--------------------------------------------------------------------------------------- adviceB
	/**
	 * This advice changes the business object property value
	 *
	 * @param $object Business
	 * @return string
	 */
	public static function adviceB($object)
	{
		return "b/advised " . $object->property;
	}

	//--------------------------------------------------------------------------------------- adviceC
	/**
	 * This advice prepends "advised" to the beginning of the object property value
	 *
	 * @param $object
	 */
	public static function adviceC($object)
	{
		$object->property = "c/advised " . $object->property;
	}

	//--------------------------------------------------------------------------------------- adviceD
	/**
	 * This advice prepends "advised" to the beginning of the object property value
	 *
	 * @param $object
	 * @return string
	 */
	public static function adviceD($object)
	{
		return "d/advised " . $object->property;
	}

	//--------------------------------------------------------------------------------------- adviceE
	/**
	 * @param $object
	 * @return string
	 */
	public static function adviceE($object)
	{
		return "e/advised " . $object->property;
	}
}

//======================================================================================== Business
/**
 * A business class example
 */
class Business
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var string
	 */
	public $property = "value";

	//------------------------------------------------------------------------------------ joinPointA
	/**
	 * @return string
	 */
	public function joinPointA()
	{
		return "A." . $this->property;
	}

	//------------------------------------------------------------------------------------ joinPointA
	/**
	 * @return string
	 */
	public function joinPointB()
	{
		return "B." . $this->property;
	}

	//------------------------------------------------------------------------------------ joinPointC
	/**
	 * @return string
	 */
	public function joinPointC()
	{
		return "C." . $this->property;
	}

}

//================================================================================== Child_Business
/**
 * Child business class example
 */
class Child_Business extends Business
{

	//------------------------------------------------------------------------------------ joinPointC
	/**
	 * @return string
	 */
	public function joinPointC()
	{
		return "Child." . parent::joinPointC();
	}

}

//================================================================================== Trait_Business
/**
 * Business trait example
 */
trait Trait_Business
{

	//------------------------------------------------------------------------------------ joinPointD
	/**
	 * @return string
	 */
	public function joinPointD()
	{
		return "d/value";
	}

	//------------------------------------------------------------------------------------ joinPointE
	/**
	 * @return string
	 */
	public function joinPointE()
	{
		return "e/value";
	}

}

//============================================================================ Trait_Business_Class
/**
 * A business class that use a business trait
 */
class Trait_Business_Class extends Child_Business
{
	use Trait_Business;

}

//====================================================================== Trait_Child_Business_Class
	/**
	 * A business class child of a class that use a business trait
	 */
class Trait_Child_Business_Class extends Trait_Business_Class
{

	//------------------------------------------------------------------------------------ joinPointE
	/**
	 * @return string
	 */
	public function joinPointE()
	{
		return "overloaded.e/value";
	}

}

}
