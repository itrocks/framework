<?php
namespace ITRocks\Framework\Dao\File\Builder;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Tests\Test;

/**
 * Unit test for Post_Files
 *
 * @group functional
 */
class Post_Files_Test extends Test
{

	//---------------------------------------------------------------------------------- filesToArray
	/**
	 * @param $element array|File
	 */
	protected function filesToArray(&$element)
	{
		if (is_array($element)) {
			foreach ($element as &$sub_element) {
				$this->filesToArray($sub_element);
			}
		}
		elseif ($element instanceof File) {
			$element = ['name' => $element->name, 'temporary_file_name' => $element->temporary_file_name];
		}
	}

	//------------------------------------------------------------------------- testAllKeysNonNumeric
	public function testAllKeysNonNumeric()
	{
		$files = ['pages' => [
			'error'    => ['background' => ['U' => 0                ]],
			'name'     => ['background' => ['U' => 'filename.pdf'   ]],
			'size'     => ['background' => ['U' => 446529           ]],
			'tmp_name' => ['background' => ['U' => '/tmp/phpXXXX'   ]],
			'type'     => ['background' => ['U' => 'application/pdf']]
		]];
		$post_files = new Post_Files(Print_Model::class);
		$result     = $post_files->appendToForm([], $files);
		$this->filesToArray($result);
		$expected = ['pages' => ['background' => [
			'U' => ['name' => 'filename.pdf', 'temporary_file_name' => '/tmp/phpXXXX']
		]]];
		static::assertEquals($expected, $result);
	}

	//------------------------------------------------------------------------ testSomeKeysNonNumeric
	public function testSomeKeysNonNumeric()
	{
		$files = ['pages' => [
			'error'    => ['background' => ['U' => 0                , '-1' => 0                ]],
			'name'     => ['background' => ['U' => 'filename1.pdf'  , '-1' => 'filename2.pdf'  ]],
			'size'     => ['background' => ['U' => 446529           , '-1' => 446529           ]],
			'tmp_name' => ['background' => ['U' => '/tmp/phpXXXX'   , '-1' => '/tmp/phpYYYY'   ]],
			'type'     => ['background' => ['U' => 'application/pdf', '-1' => 'application/pdf']]
		]];
		$post_files = new Post_Files(Print_Model::class);
		$result     = $post_files->appendToForm([], $files);
		$this->filesToArray($result);

		$expected = ['pages' => ['background' => [
			'U'  => ['name' => 'filename1.pdf', 'temporary_file_name' => '/tmp/phpXXXX'],
			'-1' => ['name' => 'filename2.pdf', 'temporary_file_name' => '/tmp/phpYYYY']
		]]];

		static::assertEquals($expected, $result);
	}

}
