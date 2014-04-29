<?php
namespace SAF\Framework\Reflection\Annotation;

use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tests\Objects\Best_Line;
use SAF\Framework\Tests\Objects\Category;
use SAF\Framework\Tests\Objects\Client;
use SAF\Framework\Tests\Objects\Item;
use SAF\Framework\Tests\Objects\Order;
use SAF\Framework\Tests\Objects\Order_Line;
use SAF\Framework\Tests\Objects\Shop;
use SAF\Framework\Tests\Test;

/**
 * Mapping annotations tests
 */
class Tests extends Test
{

	//-------------------------------------------------------------------------------- testAnnotation
	/**
	 * @param $description     string
	 * @param $class_name      string
	 * @param $property_name   string
	 * @param $annotation_name string
	 * @param $assumed_value   mixed
	 */
	private function testAnnotation(
		$description, $class_name, $property_name, $annotation_name, $assumed_value
	) {
		$property = new Reflection_Property($class_name, $property_name);
		$annotation = $property->getAnnotation($annotation_name);
		$this->assume(
			$class_name . DOT . $property_name . AT . $annotation_name . SP . DQ . $assumed_value . DQ
				. SP . '(' . $description . ')',
			$annotation->value,
			$assumed_value
		);
	}

	//----------------------------------------------------------------------------------- testForeign
	public function testForeign()
	{
		$this->testAnnotation('object', Order_Line::class, 'client', 'foreign', null); // 1A
		$this->testAnnotation('object myself', Client::class, 'client', 'foreign', null); // 1B
		$this->testAnnotation('object concurrent foreign 1', Order::class, 'client', 'foreign', null); // 1C
		$this->testAnnotation('object concurrent foreign 2', Order::class, 'delivery_client', 'foreign', null); // 1C
		$this->testAnnotation('object from component', Order_Line::class, 'order', 'foreign', 'lines'); // 1D
		$this->testAnnotation('same master object', Item::class, 'main_category', 'foreign', null); // 1E
		$this->testAnnotation('myself concurrent object', Category::class, 'main_super_category', 'foreign', null); // 1F

		$this->testAnnotation('simple collection', Order::class, 'lines', 'foreign', 'order'); // 2A

		$this->testAnnotation('map object single response', Order::class, 'salesmen', 'foreign', 'order'); // 3A
		$this->testAnnotation('map myself single response', Item::class, 'cross_selling', 'foreign', 'item'); // 3B
		$this->testAnnotation('map response', Shop::class, 'categories', 'foreign', 'shops'); // 3C
		$this->testAnnotation('map response', Category::class, 'shops', 'foreign', 'categories'); // 3C
		$this->testAnnotation('map myself response', Category::class, 'super_categories', 'foreign', 'sub_categories'); // 3D
		$this->testAnnotation('map myself response', Category::class, 'sub_categories', 'foreign', 'super_categories'); // 3D
		$this->testAnnotation('map source concurrence no reponse', Item::class, 'secondary_categories', 'foreign', 'item'); // 3E
		$this->testAnnotation('map component no response', Best_Line::class, 'lines', 'foreign', 'best_line'); // 3F
		$this->testAnnotation('map component response', Item::class, 'lines', 'foreign', 'item'); // 3G
	}

	//------------------------------------------------------------------------------- testForeignLink
	public function testForeignlink()
	{
		$this->testAnnotation('object', Order_Line::class, 'client', 'foreignlink', 'client'); // 1A
		$this->testAnnotation('object myself', Client::class, 'client', 'foreignlink', 'client'); // 1B
		$this->testAnnotation('object concurrent foreign 1', Order::class, 'client', 'foreignlink', 'client'); // 1C
		$this->testAnnotation('object concurrent foreign 2', Order::class, 'delivery_client', 'foreignlink', 'delivery_client'); // 1C
		$this->testAnnotation('object from component', Order_Line::class, 'order', 'foreignlink', 'order'); // 1D
		$this->testAnnotation('same master object', Item::class, 'main_category', 'foreignlink', 'main_category'); // 1E
		$this->testAnnotation('myself concurrent object', Category::class, 'main_super_category', 'foreignlink', 'main_super_category'); // 1F

		$this->testAnnotation('simple collection', Order::class, 'lines', 'foreignlink', 'lines'); // 2A

		$this->testAnnotation('map object single response', Order::class, 'salesmen', 'foreignlink', 'salesman'); // 3A
		$this->testAnnotation('map myself single response', Item::class, 'cross_selling', 'foreignlink', 'cross_selling'); // 3B
		$this->testAnnotation('map response', Shop::class, 'categories', 'foreignlink', 'category'); // 3C
		$this->testAnnotation('map response', Category::class, 'shops', 'foreignlink', 'shop'); // 3C
		$this->testAnnotation('map myself response', Category::class, 'super_categories', 'foreignlink', 'super_category'); // 3D
		$this->testAnnotation('map myself response', Category::class, 'sub_categories', 'foreignlink', 'sub_category'); // 3D
		$this->testAnnotation('map source concurrence no reponse', Item::class, 'secondary_categories', 'foreignlink', 'secondary_category'); // 3E
		$this->testAnnotation('map component no response', Best_Line::class, 'lines', 'foreignlink', 'line'); // 3F
		$this->testAnnotation('map component response', Item::class, 'lines', 'foreignlink', 'line'); // 3G
	}

}
