<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

use ITRocks\Framework\Tests\Test;

/**
 * Unit tests on @replaces
 */
class Replaces_Test extends Test
{

	//-------------------------------------------------------------------------------------- allTests
	/**
	 * @param $method string
	 * @param $object object
	 * @param $append string|string[]
	 */
	private function allTests($method, $object, $append = null)
	{
		if (is_string($append)) {
			$append = [$append, $append];
		}
		elseif (!isset($append)) {
			$append = ['', ''];
		}

		$object->replaced = 'to_replaced';
		$object->replacement = 'to_replacement';
		$this->assertEquals(
			['replaced' => 'to_replacement' . $append[0], 'replacement' => 'to_replacement' . $append[1]],
			$this->values($object, ['replaced', 'replacement']), $method . DOT . 'set_replacement'
		);

		$object->replaced = 'to_replaced';
		$this->assertEquals(
			['replaced' => 'to_replaced' . $append[0], 'replacement' => 'to_replaced' . $append[1]],
			$this->values($object, ['replaced', 'replacement']), $method . DOT . 'set_replaced'
		);
	}

	//------------------------------------------------------------------------------- testChildMethod
	public function testChildMethod()
	{
		$object = new Child_Method();
		$object->replaced_string = 'value';
		$this->assertEquals(
			'to_replacement', $object->getReplacedObject()->replaced, __METHOD__ . DOT . 'object.direct'
		);
		$this->assertEquals(
			'to_replacement', $object->replaced_object->replaced, __METHOD__ . DOT . 'object.replaced'
		);
		$this->assertEquals(
			'to_replacement', $object->replacement_object->replacement,
			__METHOD__ . DOT . 'object.replacement'
		);
		$this->assertEquals('value.get', $object->getReplacedString(), __METHOD__ . DOT . 'string.direct');
		// TODO HIGH Make this work (getters are not called)
		//$this->assume(__METHOD__ . DOT . 'string.replaced', $object->replaced_string, 'value.get');
		//$this->assume(__METHOD__ . DOT . 'string.replacement', $object->replacement_string, 'value.get');
		//$this->assume(__METHOD__ . DOT . 'strval', strval($object), 'value.get.to_replacement');
		//$this->assume(__METHOD__ . DOT . 'strval', strval($object), 'value.get.to_replacement');
	}

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$this->allTests('testSimple', new Simple());
		$this->allTests('testSon',    new Son());
		// TODO HIGH make replaced getters be called. Beware of replaced @link too ! If the case comes in production, you will see it with notices / errors
		//$this->allTests('testReplacedGetter', new Replaced_Getter(), '(get)');
		// TODO HIGH if replacement getters, that will throw an error that I can't understand : Parse error: syntax error, unexpected '=' in /home/baptiste/PhpStorm/ITRocks/sfkgroup/cache/compiled/itrocks-framework-reflection-annotation-tests-replaces-Replacement_Getter on line 123
		//$this->allTests('testReplacementGetter', new Replacement_Getter(), '(get)');
	}

	//---------------------------------------------------------------------------------------- values
	/**
	 * @param $object         object
	 * @param $property_names string[]
	 * @return array
	 */
	private function values($object, array $property_names)
	{
		$values = [];
		foreach ($property_names as $property_name) {
			$values[$property_name] = $object->$property_name;
		}
		return $values;
	}

}
