<?php
namespace ITRocks\Framework\Feature\Validate\Property\Tests;

use ITRocks\Framework\Feature\Validate\Property\Regex_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Test;

/**
 * The tests class enables running of unit test
 */
class Regex_Annotation_Test extends Test
{

	//--------------------------------------------------------------------- $result_ko_with_delimiter
	/**
	 * @regex #^[0-3]*$#
	 * @var string
	 */
	public string $result_ko_with_delimiter = '4567';

	//-------------------------------------------------- $result_ko_with_delimiter_and_case_sensitive
	/**
	 * @regex /php/
	 * @var string
	 */
	public string $result_ko_with_delimiter_and_case_sensitive = 'PHP';

	//------------------------------------------------------------------ $result_ko_without_delimiter
	/**
	 * @regex ^[0-3]*$
	 * @var string
	 */
	public string $result_ko_without_delimiter = '4567';

	//--------------------------------------------------------------------- $result_ok_with_delimiter
	/**
	 * @regex #^[0-3]*$#
	 * @var string
	 */
	public string $result_ok_with_delimiter = '0123';

	//------------------------------------------------ $result_ok_with_delimiter_and_case_insensitive
	/**
	 * @regex /php/i
	 * @var string
	 */
	public string $result_ok_with_delimiter_and_case_insensitive = 'PHP';

	//------------------------------------------------------------------ $result_ok_without_delimiter
	/**
	 * @regex ^[0-3]*$
	 * @var string
	 */
	public string $result_ok_without_delimiter = '0123';

	//------------------------------------------------------------------------------------------ test
	/**
	 * Regex annotation test
	 */
	public function test() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid __CLASS__ */
		$properties = (new Reflection_Class(__CLASS__))->getProperties([]);

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
			/** @noinspection PhpUnhandledExceptionInspection $property from $this and accessible */
			$assume['value'] = $property->getValue($this);

			// initialize $result
			$result           = $assume;
			$annotation       = Regex_Annotation::of($property);
			$result['result'] = $annotation->validate($this);

			static::assertEquals($assume, $result, __FUNCTION__ . '()' . SP . $property->name);
		}
	}

}
