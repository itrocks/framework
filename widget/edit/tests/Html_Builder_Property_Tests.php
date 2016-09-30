<?php
namespace SAF\Framework\Widget\Edit\Tests;

use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tests\Test;
use SAF\Framework\Widget\Edit\Html_Builder_Property;

/**
 * Unit tests for HTML builder of a property
 *
 * We focus here on used input names, which must be correctly formed
 */
class Html_Builder_Property_Tests extends Test
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
		$text = str_replace('>', '>' . LF, str_replace([LF, TAB], '', trim($text)));
		$result = [];
		foreach (explode(LF, $text) as $key => $line) {
			if (strpos($line, '<input') !== false) {
				$result[] = htmlentities($line);
			}
		}
		return $result;
	}

	//--------------------------------------------------------------------------- testBuildCollection
	public function testBuildCollection()
	{
		$object = new Has_Collection();
		$object->simple_collection = [
			new Simple_Component('O', 'one'),
			new Simple_Component('T', 'two')
		];
		$builder = new Html_Builder_Property(
			new Reflection_Property(Has_Collection::class, 'simple_collection'),
			$object->simple_collection
		);
		$assume = <<<EOT
<input name="simple_collection[id][0]" type="hidden">
<input name="simple_collection[code][0]" value="o" autocomplete="off" class="autowidth">
<input name="simple_collection[name][0]" value="one" autocomplete="off" class="autowidth" required>
<input name="simple_collection[id][1]" type="hidden">
<input name="simple_collection[code][1]" value="t" autocomplete="off" class="autowidth">
<input name="simple_collection[name][1]" value="two" autocomplete="off" class="autowidth" required>
<input name="simple_collection[id][2]" type="hidden">
<input name="simple_collection[code][2]" autocomplete="off" class="autowidth">
<input name="simple_collection[name][2]" autocomplete="off" class="autowidth" required>
EOT;
		$this->assume(__METHOD__, $this->norm($builder->build()), $this->norm($assume));
	}

	//---------------------------------------------------------------------------------- testBuildMap
	public function testBuildMap()
	{
		$object = new Has_Map();
		$object->simple_map = [
			new Simple('O', 'one'),
			new Simple('T', 'two')
		];
		$builder = new Html_Builder_Property(
			new Reflection_Property(Has_Map::class, 'simple_map'),
			$object->simple_map
		);
		$assume = <<<EOT
<input name="simple_map[0]" type="hidden" class="id">
<input value="one" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="simple_map[1]" type="hidden" class="id">
<input value="two" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="simple_map[2]" type="hidden" class="id">
<input value="" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
EOT;
		$this->assume(__METHOD__, $this->norm($builder->build()), $this->norm($assume));
	}

	//-------------------------------------------------------------------- testBuildMapIntoCollection
	public function testBuildMapIntoCollection()
	{
		$object = new Has_Map_Into_Collection();
		$object->collection_has_map = [
			new Component_Has_Map('ONE', [new Simple('O', 'one')]),
			new Component_Has_Map('TWO', [new Simple('T', 'two'), new Simple('H', 'three')])
		];
		$builder = new Html_Builder_Property(
			new Reflection_Property(Has_Map_Into_Collection::class, 'collection_has_map'),
			$object->collection_has_map
		);
		$assume = <<<EOT
<input name="collection_has_map[id][0]" type="hidden">
<input name="collection_has_map[id_composite][0]" value="" type="hidden" class="id">
<input value="" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Has_Collections" class="autowidth combo">
<input name="collection_has_map[simple_map][0][0]" type="hidden" class="id">
<input value="one" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="collection_has_map[simple_map][0][1]" type="hidden" class="id">
<input value="" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="collection_has_map[code][0]" value="one" autocomplete="off" class="autowidth">
<input name="collection_has_map[id][1]" type="hidden">
<input name="collection_has_map[id_composite][1]" value="" type="hidden" class="id">
<input value="" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Has_Collections" class="autowidth combo">
<input name="collection_has_map[simple_map][1][0]" type="hidden" class="id">
<input value="three" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="collection_has_map[simple_map][1][1]" type="hidden" class="id">
<input value="two" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="collection_has_map[simple_map][1][2]" type="hidden" class="id">
<input value="" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="collection_has_map[code][1]" value="two" autocomplete="off" class="autowidth">
<input name="collection_has_map[id][2]" type="hidden">
<input name="collection_has_map[id_composite][2]" value="" type="hidden" class="id">
<input value="" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Has_Collections" class="autowidth combo">
<input name="collection_has_map[simple_map][2][0]" type="hidden" class="id">
<input value="" autocomplete="off" data-combo-class="SAF\Framework\Widget\Edit\Tests\Simples" class="autowidth combo">
<input name="collection_has_map[code][2]" autocomplete="off" class="autowidth">
EOT;
		$this->assume(__METHOD__, $this->norm($builder->build()), $this->norm($assume));
	}

}
