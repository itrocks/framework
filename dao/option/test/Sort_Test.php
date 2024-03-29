<?php
namespace ITRocks\Framework\Dao\Option\Test;

use ITRocks\Framework\Dao\Has_Dao_Mock;
use ITRocks\Framework\Dao\Option\Sort;
use ITRocks\Framework\Tests\Test;

/**
 * Class Sort_Test
 */
class Sort_Test extends Test
{
	use Has_Dao_Mock;

	//----------------------------------------------------------------------------- getColumnProvider
	public static function getColumnProvider() : array
	{
		// Datetime is a special case, see getColumn implementation
		return [
			[Simple_Object::class, null,                         ['name', 'date'], '#0 - Get column with class as construct'],
			[Simple_Object::class, Simple_Object::class,         ['name', 'date'], '#1 - Get column with class as construct'],
			['name',               Simple_Object::class,         ['name'],         '#2 - Get column with column name as construct and class as method parameter'],
			['date',               Simple_Object::class,         ['date'],         '#3 - Get column with column date as construct and class as method parameter'],
			[['name', 'date'],     Simple_Object::class,         ['name', 'date'], '#4 - Get column with columns as construct and class as method parameter'],
			[null,                 Simple_Object::class,         ['name', 'date'], '#5 - Get column with class as method parameter'],
			['i_dont_exist',       Simple_Object::class,         [],               '#6 - Get column with non-existing column as class parameter'],
			['name_value',         Representative_Object::class, ['name_value'],   '#7 - Get column with #Representative column'],
		];
	}

	//--------------------------------------------------------------------------------- testGetColumn
	/** @dataProvider getColumnProvider */
	public function testGetColumn(
		array|string|null $construct_parameter, string|null $method_parameter, array $expected
	) : void
	{
		$sort    = new Sort($construct_parameter);
		$columns = $sort->getColumns($method_parameter);
		self::assertEquals($expected, $columns);
	}

	//------------------------------------------------------------------- testSortConstructColumnName
	public function testSortConstructColumnName() : void
	{
		$sort_class_name = new Sort(Simple_Object::class);
		$sort_property   = new Sort('id');
		$sort_properties = new Sort(['id', 'name']);
		$sort_null       = new Sort(null);
		self::assertNotNull($sort_class_name);
		self::assertNotNull($sort_property);
		self::assertNotNull($sort_properties);
		self::assertNotNull($sort_null);
	}

}
