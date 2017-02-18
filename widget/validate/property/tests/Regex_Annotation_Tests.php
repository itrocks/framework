<?php
namespace ITRocks\Framework\Widget\Validate\Property\Tests;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Widget\Validate\Property\Regex_Annotation;

/**
 * The tests class enables running of unit test
 */
class Regex_Annotation_Tests extends Test
{

	//--------------------------------------------------------------------- $result_ok_with_delimiter
	/**
	 * @regex #^[0-3]*$#
	 * @var string
	 */
	public $result_ok_with_delimiter = '0123';

	//--------------------------------------------------------------------- $result_ko_with_delimiter
	/**
	 * @regex #^[0-3]*$#
	 * @var string
	 */
	public $result_ko_with_delimiter = '4567';

	//------------------------------------------------------------------ $result_ok_without_delimiter
	/**
	 * @regex ^[0-3]*$
	 * @var string
	 */
	public $result_ok_without_delimiter = '0123';

	//------------------------------------------------------------------ $result_ko_without_delimiter
	/**
	 * @regex ^[0-3]*$
	 * @var string
	 */
	public $result_ko_without_delimiter = '4567';

	//-------------------------------------------------- $result_ko_with_delimiter_and_case_sensitive
	/**
	 * @regex /php/
	 * @var string
	 */
	public $result_ko_with_delimiter_and_case_sensitive = 'PHP';

	//------------------------------------------------ $result_ok_with_delimiter_and_case_insensitive
	/**
	 * @regex /php/i
	 * @var string
	 */
	public $result_ok_with_delimiter_and_case_insensitive = 'PHP';

	//------------------------------------------------------------------------------------------ test
	/**
	 * @return boolean test regex annotation
	 */
	public function test()
	{
		$properties = (new Reflection_Class(__CLASS__))->getProperties();

		$ok = true;

		foreach ($properties as $property) {

			if (!Regex_Annotation::of($property)->value) {
				continue;
			}

			// initialize $assume
			switch (substr($property->name, 0, 9)) {
				case 'result_ok':
					$assume = ['result' => true];
					break;
				case 'result_ko':
					$assume = ['result' => false];
					break;
				default:
					continue 2;
			}
			$assume['pattern'] = Regex_Annotation::of($property)->value;
			$assume['value']   = $property->getValue($this);

			// initialize $result
			$result = $assume;
			$result['result'] = Regex_Annotation::of($property)->validate($this);

			$ok &= $this->assume(__FUNCTION__ . '()' . SP . $property->name, $result, $assume);

		}

		return $ok;
	}

}
