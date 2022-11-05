<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests;

use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Foreignlink_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Objects\Best_Line;
use ITRocks\Framework\Tests\Objects\Category;
use ITRocks\Framework\Tests\Objects\Client;
use ITRocks\Framework\Tests\Objects\Item;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Order_Line;
use ITRocks\Framework\Tests\Objects\Shop;
use ITRocks\Framework\Tests\Test;
use ReflectionException;

/**
 * Mapping annotations tests
 */
class Annotation_Test extends Test
{

	//--------------------------------------------------------------------------- providerBeforeWrite
	/**
	 * @return string[][]
	 * @see testBeforeWrite
	 */
	public function providerBeforeWrite() : array
	{
		return [
			'parsed' => [Item::class, Item::class . '::beforeWrite', 'before_write'],
			'cached' => [Item::class, Item::class . '::beforeWrite', 'before_write']
		];
	}

	//------------------------------------------------------------------------------- providerForeign
	/**
	 * @return array
	 * @see testBeforeWrite
	 */
	public function providerForeign() : array
	{
		return [
			'object'                             => [Order_Line::class, 'client', null], // 1A
			'object myself'                      => [Client::class, 'client', null], // 1B
			'object concurrent foreign 1'        => [Order::class, 'client', null], // 1C
			'object concurrent foreign 2'        => [Order::class, 'delivery_client', null], // 1C
			'object from component'              => [Order_Line::class, 'order', 'lines'], // 1D
			'same master object'                 => [Item::class, 'main_category', null], // 1E
			'myself concurrent object'           => [Category::class, 'main_super_category', null], // 1F
			'simple collection'                  => [Order::class, 'lines', 'order'], // 2A
			'map object single response'         => [Order::class, 'salesmen', 'order'], // 3A
			'map myself single response'         => [Item::class, 'cross_selling', 'item'], // 3B
			'map response Shop'                  => [Shop::class, 'categories', 'shops'], // 3C
			'map response Cat'                   => [Category::class, 'shops', 'categories'], // 3C
			'map myself response sub'            => [Category::class, 'super_categories', 'sub_categories'],// 3D
			'map myself response super'          => [Category::class, 'sub_categories', 'super_categories'],// 3D
			'map source concurrence no response' => [Item::class, 'secondary_categories', 'item'], // 3E
			'map component no response'          => [Best_Line::class, 'lines', 'best_line'], // 3F
			'map component response'             => [Item::class, 'lines', 'item'], // 3G
		];
	}

	//--------------------------------------------------------------------------- providerForeignlink
	/**
	 * @return string[][]
	 */
	public function providerForeignlink() : array
	{
		return [
			'object'                             => [Order_Line::class, 'client', 'client'], // 1A
			'object myself'                      => [Client::class, 'client', 'client'], // 1B
			'object concurrent foreign 1'        => [Order::class, 'client', 'client'], // 1C
			'object concurrent foreign 2'        => [Order::class, 'delivery_client', 'delivery_client'], // 1C
			'object from component'              => [Order_Line::class, 'order', 'order'], // 1D
			'same master object'                 => [Item::class, 'main_category', 'main_category'], // 1E
			'myself concurrent object'           => [Category::class, 'main_super_category', 'main_super_category'], // 1F
			'simple collection'                  => [Order::class, 'lines', 'lines'], // 2A
			'map object single response'         => [Order::class, 'salesmen', 'salesman'], // 3A
			'map myself single response'         => [Item::class, 'cross_selling', 'cross_selling'], // 3B
			'map response Shop'                  => [Shop::class, 'categories', 'category'], // 3C
			'map response Cat'                   => [Category::class, 'shops', 'shop'], // 3C
			'map myself response sub'            => [Category::class, 'super_categories', 'super_category'],// 3D
			'map myself response super'          => [Category::class, 'sub_categories', 'sub_category'], // 3D
			'map source concurrence no response' => [Item::class, 'secondary_categories', 'secondary_category'], // 3E
			'map component no response'          => [Best_Line::class, 'lines', 'line'], // 3F
			'map component response'             => [Item::class, 'lines', 'line'], // 3G
		];
	}

	//------------------------------------------------------------------------------- testBeforeWrite
	/**
	 * @dataProvider providerBeforeWrite
	 * @param $class_name      string
	 * @param $assumed_value   mixed
	 * @param $annotation_name string
	 * @throws ReflectionException
	 */
	public function testBeforeWrite(string $class_name, mixed $assumed_value, string $annotation_name)
		: void
	{
		$class      = new Reflection_Class($class_name);
		$annotation = $class->getAnnotation($annotation_name);
		static::assertEquals(
			[Method_Annotation::class, $assumed_value],
			[get_class($annotation), $annotation->value],
			$class_name . AT . $annotation_name
		);
	}

	//----------------------------------------------------------------------------------- testForeign
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @dataProvider providerForeign
	 * @param $class_name      string
	 * @param $property_name   string
	 * @param $assumed_value   mixed
	 * @param $annotation_name string
	 * @param $assumed_class   string
	 */
	public function testForeign(
		string $class_name, string $property_name, mixed $assumed_value, string $annotation_name = '',
		string $assumed_class = ''
	) : void
	{
		if (!$assumed_class) {
			$assumed_class = Foreign_Annotation::class;
		}
		if (!$annotation_name) {
			$annotation_name = Foreign_Annotation::ANNOTATION;
		}
		/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
		$property   = new Reflection_Property($class_name, $property_name);
		$annotation = $property->getAnnotation($annotation_name);
		static::assertEquals(
			[$assumed_class, $assumed_value],
			[get_class($annotation), $annotation->value],
			$class_name . AT . $annotation_name
		);
	}

	//------------------------------------------------------------------------------- testForeignlink
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @dataProvider providerForeignlink
	 * @param $class_name      string
	 * @param $property_name   string
	 * @param $assumed_value   mixed
	 * @param $annotation_name string
	 * @param $assumed_class   string
	 */
	public function testForeignlink(
		string $class_name, string $property_name, mixed $assumed_value, string $annotation_name = '',
		string $assumed_class = ''
	) : void
	{
		if (!$assumed_class) {
			$assumed_class = Foreignlink_Annotation::class;
		}
		if (!$annotation_name) {
			$annotation_name = 'foreignlink';
		}
		/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
		$property   = new Reflection_Property($class_name, $property_name);
		$annotation = $property->getAnnotation($annotation_name);
		static::assertEquals(
			[$assumed_class, $assumed_value],
			[get_class($annotation), $annotation->value],
			$class_name . AT . $annotation_name
		);
	}

}
