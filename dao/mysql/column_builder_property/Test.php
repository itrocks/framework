<?php
namespace ITRocks\Framework\Dao\Mysql\Column_Builder_Property;

use ITRocks\Framework\Dao\Mysql\Column_Builder_Property;
use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Feature\Validate\Property\Max_Value;
use ITRocks\Framework\Feature\Validate\Property\Min_Value;
use ITRocks\Framework\Reflection\Attribute\Property\Decimals;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Tests;
use ReflectionException;

/**
 * Column builder property tests
 */
class Test extends Tests\Test
{

	//------------------------------------------------------------------------------------- $decimal1
	/**
	 * fixed precision decimal
	 *
	 * @assume decimal(65,2) unsigned
	 */
	#[Decimals(2)]
	public float $decimal1;

	//------------------------------------------------------------------------------------ $decimal1b
	/**
	 * fixed precision decimal, signed
	 *
	 * @assume decimal(65,2)
	 * @signed
	 */
	#[Decimals(2)]
	public float $decimal1b;

	//------------------------------------------------------------------------------------- $decimal2
	/**
	 * fixed precision decimal with max-length
	 *
	 * @assume decimal(9,2) unsigned
	 */
	#[Decimals(2), Max_Length(10)]
	public float $decimal2;

	//------------------------------------------------------------------------------------- $decimal3
	/**
	 * fixed precision decimal with max-length, signed
	 *
	 * @assume decimal(8,2)
	 * @signed
	 */
	#[Decimals(2), Max_Length(10)]
	public float $decimal3;

	//------------------------------------------------------------------------------------- $decimal4
	/**
	 * fixed precision decimal with max-value
	 *
	 * @assume decimal(5,2) unsigned
	 */
	#[Decimals(2), Max_Value(495.34)]
	public float $decimal4;

	//------------------------------------------------------------------------------------ $decimal4b
	/**
	 * fixed precision decimal with max-value
	 *
	 * @assume decimal(5,2) unsigned
	 */
	#[Decimals(2), Max_Value(495.3)]
	public float $decimal4b;

	//------------------------------------------------------------------------------------ $decimal4c
	/**
	 * fixed precision decimal with max-value
	 *
	 * @assume decimal(5,2) unsigned
	 */
	#[Decimals(2), Max_Value(495)]
	public float $decimal4c;

	//------------------------------------------------------------------------------------- $decimal5
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume decimal(5,2)
	 * @signed
	 */
	#[Decimals(2), Max_Value(495.34), Min_Value(-495)]
	public float $decimal5;

	//------------------------------------------------------------------------------------ $decimal5b
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume decimal(5,2)
	 * @signed
	 */
	#[Decimals(2), Max_Value(495), Min_Value(-495.3)]
	public float $decimal5b;

	//------------------------------------------------------------------------------------ $decimal5c
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume decimal(5,2)
	 * @signed
	 */
	#[Decimals(2), Max_Value(495), Min_Value(-495)]
	public float $decimal5c;

	//------------------------------------------------------------------------------------ $decimal5d
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume decimal(5,2)
	 * @signed
	 */
	#[Decimals(2), Max_Value(-495), Min_Value(-495.34)]
	public float $decimal5d;

	//------------------------------------------------------------------------------------ $decimal5e
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume decimal(5,2)
	 * @signed
	 */
	#[Decimals(2), Max_Value(-494.3), Min_Value(-495)]
	public float $decimal5e;

	//------------------------------------------------------------------------------------ $decimal5f
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume decimal(5,2)
	 * @signed
	 */
	#[Decimals(2), Max_Value(-495), Min_Value(-525)]
	public float $decimal5f;

	//------------------------------------------------------------------------------------ $decimal5g
	/**
	 * fixed precision decimal with max-value, signed
	 *
	 * @assume decimal(65,2)
	 * @signed
	 */
	#[Decimals(2), Max_Value(-495)]
	public float $decimal5g;

	//------------------------------------------------------------------------------------- $integer1
	/**
	 * min-value only
	 *
	 * @assume smallint(5)
	 */
	#[Min_Value(-2300)]
	public int $integer1;

	//------------------------------------------------------------------------------------- $integer2
	/**
	 * max-value and signed : smallint
	 *
	 * @assume smallint(5)
	 * @signed
	 */
	#[Max_Value(32767)]
	public int $integer2;

	//------------------------------------------------------------------------------------ $integer2b
	/**
	 * max-value and signed : grown
	 *
	 * @assume mediumint(7)
	 * @signed
	 */
	#[Max_Value(32768)]
	public int $integer2b;

	//------------------------------------------------------------------------------------- $integer3
	/**
	 * max-value unsigned : tinyint
	 *
	 * @assume tinyint(3) unsigned
	 */
	#[Max_Value(255)]
	public int $integer3;

	//------------------------------------------------------------------------------------ $integer3b
	/**
	 * max-value unsigned : smallint
	 *
	 * @assume smallint(5) unsigned
	 */
	#[Max_Value(65535)]
	public int $integer3b;

	//------------------------------------------------------------------------------------ $integer3c
	/**
	 * max-value unsigned : mediumint
	 *
	 * @assume mediumint(8) unsigned
	 */
	#[Max_Value(16777215)]
	public int $integer3c;

	//------------------------------------------------------------------------------------ $integer3d
	/**
	 * max-value unsigned : int
	 *
	 * @assume int(10) unsigned
	 */
	#[Max_Value(4294967295)]
	public int $integer3d;

	//------------------------------------------------------------------------------------ $integer3e
	/**
	 * max-value unsigned : bigint
	 *
	 * @assume bigint(18) unsigned
	 */
	#[Max_Value(4294967296)]
	public int $integer3e;

	//------------------------------------------------------------------------------------- $integer4
	/** @assume bigint(18) unsigned */
	public int $integer4;

	//------------------------------------------------------------------------------------- $integer5
	/**
	 * @assume bigint(18)
	 * @signed
	 */
	public int $integer5;

	//--------------------------------------------------------------------------------- propertyTests
	/**
	 * @param $property_prefix string @values decimal, integer
	 * @throws ReflectionException
	 */
	protected function propertyTests(string $property_prefix) : void
	{
		$property_type_to_mysql = (new Reflection_Method(
			Column_Builder_Property::class, 'propertyTypeToMysql'
		));
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
	/** @throws ReflectionException */
	public function testDecimal() : void
	{
		$this->propertyTests('decimal');
	}

	//----------------------------------------------------------------------------------- testInteger
	/** @throws ReflectionException */
	public function testInteger() : void
	{
		$this->propertyTests('integer');
	}

}
