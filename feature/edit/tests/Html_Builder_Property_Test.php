<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Test;

/**
 * Unit tests for HTML builder of a property
 *
 * We focus here on used input names, which must be correctly formed
 */
class Html_Builder_Property_Test extends Test
{

	//------------------------------------------------------------------------------------------ norm
	/**
	 * Normalize text
	 *
	 * @param $text string
	 * @return string[]
	 */
	private function norm($text)
	{
		$result = [];
		foreach (explode('>', $text) as $line) {
			if (str_contains($line, '<input') && str_contains($line, SP . 'name=' . DQ)) {
				$result[] = mParse($line, SP . 'name=' . DQ, DQ);
			}
		}
		return $result;
	}

	//--------------------------------------------------------------------------- testBuildCollection
	public function testBuildCollection()
	{
		$object                    = new Has_Collection();
		$object->simple_collection = [
			new Simple_Component('O', 'one'),
			new Simple_Component('T', 'two')
		];
		/** @noinspection PhpUnhandledExceptionInspection valid constants */
		$builder = new Html_Builder_Property(
			new Reflection_Property(Has_Collection::class, 'simple_collection'),
			$object->simple_collection
		);

		$assume = [
			'simple_collection[id][0]',
			'simple_collection[code][0]',
			'simple_collection[name][0]',
			'simple_collection[id][1]',
			'simple_collection[code][1]',
			'simple_collection[name][1]',
			'simple_collection[id][2]',
			'simple_collection[code][2]',
			'simple_collection[name][2]',
		];
		static::assertEquals($assume, $this->norm($builder->build()));
	}

	//---------------------------------------------------------------------------------- testBuildMap
	public function testBuildMap()
	{
		$object             = new Has_Map();
		$object->simple_map = [
			new Simple('O', 'one'),
			new Simple('T', 'two')
		];
		/** @noinspection PhpUnhandledExceptionInspection valid constants */
		$builder = new Html_Builder_Property(
			new Reflection_Property(Has_Map::class, 'simple_map'),
			$object->simple_map
		);
		$assume = [
			'simple_map[0]',
			'simple_map[1]',
			'simple_map[2]',
		];
		static::assertEquals($assume, $this->norm($builder->build()));
	}

	//-------------------------------------------------------------------- testBuildMapIntoCollection
	public function testBuildMapIntoCollection()
	{
		$object                     = new Has_Map_Into_Collection();
		$object->collection_has_map = [
			new Component_Has_Map('ONE', [new Simple('O', 'one')]),
			new Component_Has_Map('TWO', [new Simple('T', 'two'), new Simple('H', 'three')])
		];

		/** @noinspection PhpUnhandledExceptionInspection valid constants */
		$builder = new Html_Builder_Property(
			new Reflection_Property(Has_Map_Into_Collection::class, 'collection_has_map'),
			$object->collection_has_map
		);

		$assume = [
			'collection_has_map[id][0]',
			'collection_has_map[id_composite][0]',
			'collection_has_map[simple_map][0][0]',
			'collection_has_map[simple_map][0][1]',
			'collection_has_map[code][0]',
			'collection_has_map[id][1]',
			'collection_has_map[id_composite][1]',
			'collection_has_map[simple_map][1][0]',
			'collection_has_map[simple_map][1][1]',
			'collection_has_map[simple_map][1][2]',
			'collection_has_map[code][1]',
			'collection_has_map[id][2]',
			'collection_has_map[id_composite][2]',
			'collection_has_map[simple_map][2][0]',
			'collection_has_map[code][2]',
		];
		static::assertEquals($assume, $this->norm($builder->build()));
	}

}
