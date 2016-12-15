<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

use ITRocks\Framework\Tests\Test;

/**
 * Unit tests on @replaces
 */
class Tests extends Test
{

	//-------------------------------------------------------------------------------------- allTests
	/**
	 * @param $method string
	 * @param $object object
	 * @param $append string[]
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
		$this->assume(
			$method . DOT . 'set_replacement',
			$this->values($object, ['replaced', 'replacement']),
			['replaced' => 'to_replacement' . $append[0], 'replacement' => 'to_replacement' . $append[1]]
		);

		$object->replaced = 'to_replaced';
		$this->assume(
			$method . DOT . 'set_replaced',
			$this->values($object, ['replaced', 'replacement']),
			['replaced' => 'to_replaced' . $append[0], 'replacement' => 'to_replaced' . $append[1]]
		);
	}

	//------------------------------------------------------------------------------- testChildMethod
	public function testChildMethod()
	{
		$object = new Child_Method();
		$object->replaced_string = 'value';
		$this->assume(
			__METHOD__ . DOT . 'object.direct', $object->getReplacedObject()->replaced, 'to_replacement'
		);
		$this->assume(
			__METHOD__ . DOT . 'object.replaced', $object->replaced_object->replaced, 'to_replacement'
		);
		$this->assume(
			__METHOD__ . DOT . 'object.replacement',
			$object->replacement_object->replacement,
			'to_replacement'
		);
		$this->assume(__METHOD__ . DOT . 'string.direct', $object->getReplacedString(), 'value.get');
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
	private function values($object, $property_names)
	{
		$values = [];
		foreach ($property_names as $property_name) {
			$values[$property_name] = $object->$property_name;
		}
		return $values;
	}

}
