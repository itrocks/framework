<?php
namespace ITRocks\Framework\Dao\Mysql\Column_Builder_Property;

use ITRocks\Framework\Dao\Mysql\Column_Builder_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Tests;

/**
 * Column builder property tests
 */
class Test extends Tests\Test
{

	//------------------------------------------------------------------------------------- $decimal1
	/**
	 * fixed precision decimal
	 *
	 * @assume    decimal(65,2) unsigned
	 * @precision 2
	 * @var float
	 */
	public $decimal1;

	//------------------------------------------------------------------------------------ $decimal1b
	/**
	 * fixed precision decimal, signed
	 *
	 * @assume    decimal(65,2)
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal1b;

	//------------------------------------------------------------------------------------- $decimal2
	/**
	 * fixed precision decimal with max-length
	 *
	 * @assume     decimal(9,2) unsigned
	 * @max_length 10
	 * @precision  2
	 * @var float
	 */
	public $decimal2;

	//------------------------------------------------------------------------------------- $decimal3
	/**
	 * fixed precision decimal with max-length, signed
	 *
	 * @assume     decimal(8,2)
	 * @max_length 10
	 * @precision  2
	 * @signed
	 * @var float
	 */
	public $decimal3;

	//------------------------------------------------------------------------------------- $decimal4
	/**
	 * fixed precision decimal with max-value
	 *
	 * @assume    decimal(5,2) unsigned
	 * @max_value 495.34
	 * @precision 2
	 * @var float
	 */
	public $decimal4;

	//------------------------------------------------------------------------------------ $decimal4b
	/**
	 * fixed precision decimal with max-value
	 *
	 * @assume    decimal(5,2) unsigned
	 * @max_value 495.3
	 * @precision 2
	 * @var float
	 */
	public $decimal4b;

	//------------------------------------------------------------------------------------ $decimal4c
	/**
	 * fixed precision decimal with max-value
	 *
	 * @assume    decimal(5,2) unsigned
	 * @max_value 495
	 * @precision 2
	 * @var float
	 */
	public $decimal4c;

	//------------------------------------------------------------------------------------- $decimal5
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume    decimal(5,2)
	 * @max_value 495.34
	 * @min_value -495
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal5;

	//------------------------------------------------------------------------------------ $decimal5b
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume    decimal(5,2)
	 * @max_value 495
	 * @min_value -495.3
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal5b;

	//------------------------------------------------------------------------------------ $decimal5c
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume    decimal(5,2)
	 * @max_value 495
	 * @min_value -495
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal5c;

	//------------------------------------------------------------------------------------ $decimal5d
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume    decimal(5,2)
	 * @max_value -495
	 * @min_value -495.34
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal5d;

	//------------------------------------------------------------------------------------ $decimal5e
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume    decimal(5,2)
	 * @max_value -494.3
	 * @min_value -495
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal5e;

	//------------------------------------------------------------------------------------ $decimal5f
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume    decimal(5,2)
	 * @max_value -495
	 * @min_value -525
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal5f;

	//------------------------------------------------------------------------------------ $decimal5g
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume    decimal(65,2)
	 * @max_value -495
	 * @precision 2
	 * @signed
	 * @var float
	 */
	public $decimal5g;

	//------------------------------------------------------------------------------------- $integer1
	/**
	 * min-value only
	 *
	 * @assume    smallint(5)
	 * @min_value -2300
	 * @var integer
	 */
	public $integer1;

	//------------------------------------------------------------------------------------- $integer2
	/**
	 * max-value and signed : smallint
	 *
	 * @assume    smallint(5)
	 * @max_value 32767
	 * @signed
	 * @var integer
	 */
	public $integer2;

	//------------------------------------------------------------------------------------ $integer2b
	/**
	 * max-value and signed : grown
	 *
	 * @assume    mediumint(7)
	 * @max_value 32768
	 * @signed
	 * @var integer
	 */
	public $integer2b;

	//------------------------------------------------------------------------------------- $integer3
	/**
	 * max-value unsigned : tinyint
	 *
	 * @assume    tinyint(3) unsigned
	 * @max_value 255
	 * @var integer
	 */
	public $integer3;

	//------------------------------------------------------------------------------------ $integer3b
	/**
	 * max-value unsigned : smallint
	 *
	 * @assume    smallint(5) unsigned
	 * @max_value 65535
	 * @var integer
	 */
	public $integer3b;

	//------------------------------------------------------------------------------------ $integer3c
	/**
	 * max-value unsigned : mediumint
	 *
	 * @assume    mediumint(8) unsigned
	 * @max_value 16777215
	 * @var integer
	 */
	public $integer3c;

	//------------------------------------------------------------------------------------ $integer3d
	/**
	 * max-value unsigned : int
	 *
	 * @assume    int(10) unsigned
	 * @max_value 4294967295
	 * @var integer
	 */
	public $integer3d;

	//------------------------------------------------------------------------------------ $integer3e
	/**
	 * max-value unsigned : bigint
	 *
	 * @assume    bigint(18) unsigned
	 * @max_value 4294967296
	 * @var integer
	 */
	public $integer3e;

	//------------------------------------------------------------------------------------- $integer4
	/**
	 * @assume bigint(18) unsigned
	 * @var integer
	 */
	public $integer4;

	//------------------------------------------------------------------------------------- $integer5
	/**
	 * @assume bigint(18)
	 * @signed
	 * @var integer
	 */
	public $integer5;

	//--------------------------------------------------------------------------------- propertyTests
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property_prefix string @values decimal, integer
	 */
	protected function propertyTests($property_prefix)
	{
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$property_type_to_mysql = (new Reflection_Method(
			Column_Builder_Property::class, 'propertyTypeToMysql'
		));
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($this))->getProperties([]) as $property) {
			if (str_starts_with($property->name, $property_prefix)) {
				$assume = $property->getAnnotation('assume')->value;
				if (isset($assume)) {
					$check = $property_type_to_mysql->invoke(null, $property);
					static::assertEquals($assume, $check, $property->name);
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- testDecimal
	public function testDecimal()
	{
		$this->propertyTests('decimal');
	}

	//----------------------------------------------------------------------------------- testInteger
	public function testInteger()
	{
		$this->propertyTests('integer');
	}

}
