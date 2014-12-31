<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Dao;
use SAF\Framework\Dao\Option;
use SAF\Framework\Tests\Test;

/**
 * Class annotations unit tests
 *
 * @after_write localAfterWrite
 * @after_write Tests::distantAfterWrite
 * @before_write localBeforeWrite
 * @before_write Tests::distantBeforeWrite
 * @set Unit_Tests
 */
class Tests extends Test
{

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var string
	 */
	public $data;

	//----------------------------------------------------------------------------- distantAfterWrite
	/**
	 * @param $tests   Tests
	 * @param $options Option[]
	 */
	public static function distantAfterWrite(Tests $tests, $options)
	{
		$tests->dynamic('disafter', $options);
	}

	//---------------------------------------------------------------------------- distantBeforeWrite
	/**
	 * @param $tests   Tests
	 * @param $options Option[]
	 */
	public static function distantBeforeWrite(Tests $tests, $options)
	{
		$tests->dynamic('disbefore', $options);
	}

	//--------------------------------------------------------------------------------------- dynamic
	/**
	 * @param $text    string
	 * @param $options Option[]
	 */
	private function dynamic($text, $options)
	{
		$this->data .= '+' . $text . '(';
		if (count($options) == 1) {
			foreach ($options as $option) {
				if ($option instanceof Option\Only) {
					$this->data .= join(',', $option->properties);
				}
			}
		}
		$this->data .= ')';
	}

	//------------------------------------------------------------------------------- localAfterWrite
	/**
	 * @param $options Option[]
	 */
	public function localAfterWrite($options)
	{
		$this->dynamic('locafter', $options);
	}

	//------------------------------------------------------------------------------ localBeforeWrite
	/**
	 * @param $options Option[]
	 */
	public function localBeforeWrite($options)
	{
		$this->dynamic('locbefore', $options);
	}

	//-------------------------------------------------------------------------- testWriteAnnotations
	public function testWriteAnnotations()
	{
		$tests = new Tests();
		$tests->data = 'test';
		Dao::write($tests, [Dao::only(['data'])]);
		Dao::delete($tests);
		$this->assume(
			__METHOD__,
			$tests->data,
			'test+locbefore(data)+disbefore(data)+locafter(data)+disafter(data)'
		);
	}

}
