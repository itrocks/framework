<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Tests\Test;

/**
 * Class annotations unit tests
 *
 * @after_commit localAfterCommit1
 * @after_commit localAfterCommit2
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
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public static function distantAfterWrite(
		Tests $tests, /* @noinspection PhpUnusedParameterInspection */ Data_Link $link, array $options
	) {
		$tests->dynamic('dis-after', $options);
	}

	//---------------------------------------------------------------------------- distantBeforeWrite
	/**
	 * @param $tests   Tests
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public static function distantBeforeWrite(
		Tests $tests, /* @noinspection PhpUnusedParameterInspection */ Data_Link $link, array $options
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
		/* @noinspection PhpUnusedParameterInspection */ Data_Link $link, array $options
	) {
		$this->dynamic('loc-after-commit1', $options);
	}

	//----------------------------------------------------------------------------- localAfterCommit2
	/**
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public function localAfterCommit2(
		/* @noinspection PhpUnusedParameterInspection */ Data_Link $link, array $options
	) {
		$this->dynamic('loc-after-commit2', $options);
	}

	//------------------------------------------------------------------------------- localAfterWrite
	/**
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public function localAfterWrite(
		/* @noinspection PhpUnusedParameterInspection */ Data_Link $link, array $options
	) {
		$this->dynamic('loc-after', $options);
	}

	//------------------------------------------------------------------------------ localBeforeWrite
	/**
	 * @param $link    Data_Link
	 * @param $options Option[]
	 */
	public function localBeforeWrite(
		/* @noinspection PhpUnusedParameterInspection */ Data_Link $link, array $options)
	{
		$this->dynamic('loc-before', $options);
	}

	//-------------------------------------------------------------------------- testWriteAnnotations
	public function testWriteAnnotations()
	{
		$tests       = new Tests();
		$tests->data = 'test';
		Dao::begin();
		Dao::write($tests, Dao::only('data'));
		Dao::delete($tests);
		Dao::commit();
		$this->assume(
			__METHOD__ . ' with commit',
			$tests->data,
			'test'
			. '+loc-before(data)+dis-before(data)'
			. '+loc-after(data)+dis-after(data)'
			. '+loc-after-commit1(data)+loc-after-commit2(data)'
		);

		$tests       = new Tests();
		$tests->data = 'test';
		Dao::begin();
		Dao::write($tests, Dao::only('data'));
		Dao::delete($tests);
		Dao::rollback();
		$this->assume(
			__METHOD__ . ' with rollback',
			$tests->data,
			'test'
			. '+loc-before(data)+dis-before(data)'
			. '+loc-after(data)+dis-after(data)'
		);
	}

}
