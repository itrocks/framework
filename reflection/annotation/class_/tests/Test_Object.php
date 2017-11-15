<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_\Tests;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Option;

/**
 * Class annotations unit tests
 *
 * @after_commit localAfterCommit1
 * @after_commit localAfterCommit2
 * @after_write  localAfterWrite
 * @after_write  Test_Object::distantAfterWrite
 * @before_write localBeforeWrite
 * @before_write Test_Object::distantBeforeWrite
 * @store_name   unit_tests
 */
class Test_Object
{

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var string
	 */
	public $data;

	//----------------------------------------------------------------------------- distantAfterWrite
	/**
	 * @param $tests   Test_Object
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public static function distantAfterWrite(
		Test_Object $tests, /* @noinspection PhpUnusedParameterInspection */ Data_Link $link,
		array $options
	) {
		$tests->dynamic('dis-after', $options);
	}

	//---------------------------------------------------------------------------- distantBeforeWrite
	/**
	 * @param $tests   Test_Object
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public static function distantBeforeWrite(
		Test_Object $tests, /* @noinspection PhpUnusedParameterInspection */ Data_Link $link,
		array $options
	) {
		$tests->dynamic('dis-before', $options);
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

	//----------------------------------------------------------------------------- localAfterCommit1
	/**
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public function localAfterCommit1(
		/* @noinspection PhpUnusedParameterInspection */
		Data_Link $link, array $options
	)
	{
		$this->dynamic('loc-after-commit1', $options);
	}

	//----------------------------------------------------------------------------- localAfterCommit2
	/**
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public function localAfterCommit2(
		/* @noinspection PhpUnusedParameterInspection */
		Data_Link $link, array $options
	)
	{
		$this->dynamic('loc-after-commit2', $options);
	}

	//------------------------------------------------------------------------------- localAfterWrite
	/**
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public function localAfterWrite(
		/* @noinspection PhpUnusedParameterInspection */
		Data_Link $link, array $options
	)
	{
		$this->dynamic('loc-after', $options);
	}

	//------------------------------------------------------------------------------ localBeforeWrite
	/**
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public function localBeforeWrite(
		/* @noinspection PhpUnusedParameterInspection */
		Data_Link $link, array $options
	)
	{
		$this->dynamic('loc-before', $options);
	}

}
