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

	//------------------------------------------------------------------------------------- $integer1
	/**
	 * @assume smallint(5)
	 * @min_value -2300
	 * @var integer
	 */
	public $integer1;

	//------------------------------------------------------------------------------------- $integer2
	/**
	 * @assume mediumint(7)
	 * @max_value 65535
	 * @signed
	 * @var integer
	 */
	public $integer2;

	//------------------------------------------------------------------------------------- $integer3
	/**
	 * @assume smallint(5) unsigned
	 * @max_value 65535
	 * @var integer
	 */
	public $integer3;

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

	//----------------------------------------------------------------------------------- testInteger
	public function testInteger()
	{
		$property_type_to_mysql = (new Reflection_Method(
			Column_Builder_Property::class, 'propertyTypeToMysql'
		));
		$property_type_to_mysql->setAccessible(true);
		foreach ((new Reflection_Class(get_class($this)))->getProperties() as $property) {
			if (beginsWith($property->name, 'integer')) {
				$assume = $property->getAnnotation('assume')->value;
				if (isset($assume)) {
					$check = $property_type_to_mysql->invoke(null, $property);
					$this->assume($property->name, $check, $assume);
				}
			}
		}
	}

}
