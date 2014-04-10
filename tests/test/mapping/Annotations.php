<?php
namespace SAF\Tests\Test\Mapping;

use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Test;

/**
 * Mapping annotations tests
 */
class Annotations extends Test
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
		$class_name = 'SAF\Tests' . BS . $class_name;
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
		$this->testAnnotation('object', 'Order_Line', 'client', 'foreign', ''); // 1A
		$this->testAnnotation('object myself', 'Client', 'client', 'foreign', ''); // 1B
		$this->testAnnotation('object concurrent foreign 1', 'Order', 'client', 'foreign', ''); // 1C
		$this->testAnnotation('object concurrent foreign 2', 'Order', 'delivery_client', 'foreign', ''); // 1C
		$this->testAnnotation('object from component', 'Order_Line', 'order', 'foreign', 'lines'); // 1D
		$this->testAnnotation('same master object', 'Item', 'main_category', 'foreign', ''); // 1E
		$this->testAnnotation('myself concurrent object', 'Category', 'main_super_category', 'foreign', ''); // 1F

		$this->testAnnotation('simple collection', 'Order', 'lines', 'foreign', 'order'); // 2A

		$this->testAnnotation('map object single response', 'Order', 'salesmen', 'foreign', 'order'); // 3A
		$this->testAnnotation('map myself single response', 'Item', 'cross_selling', 'foreign', 'item'); // 3B
		$this->testAnnotation('map response', 'Shop', 'categories', 'foreign', 'shops'); // 3C
		$this->testAnnotation('map response', 'Category', 'shops', 'foreign', 'categories'); // 3C
		$this->testAnnotation('map myself response', 'Category', 'super_categories', 'foreign', 'sub_categories'); // 3D
		$this->testAnnotation('map myself response', 'Category', 'sub_categories', 'foreign', 'super_categories'); // 3D
		$this->testAnnotation('map source concurrence no reponse', 'Item', 'secondary_categories', 'foreign', 'item'); // 3E
		$this->testAnnotation('map component no response', 'Best_Line', 'lines', 'foreign', 'best_line'); // 3F
		$this->testAnnotation('map component response', 'Item', 'lines', 'foreign', 'item'); // 3G
	}

	//------------------------------------------------------------------------------- testForeignLink
	public function testForeignlink()
	{
		$this->testAnnotation('object', 'Order_Line', 'client', 'foreignlink', 'client'); // 1A
		$this->testAnnotation('object myself', 'Client', 'client', 'foreignlink', 'client'); // 1B
		$this->testAnnotation('object concurrent foreign 1', 'Order', 'client', 'foreignlink', 'client'); // 1C
		$this->testAnnotation('object concurrent foreign 2', 'Order', 'delivery_client', 'foreignlink', 'delivery_client'); // 1C
		$this->testAnnotation('object from component', 'Order_Line', 'order', 'foreignlink', 'order'); // 1D
		$this->testAnnotation('same master object', 'Item', 'main_category', 'foreignlink', 'main_category'); // 1E
		$this->testAnnotation('myself concurrent object', 'Category', 'main_super_category', 'foreignlink', 'main_super_category'); // 1F

		$this->testAnnotation('simple collection', 'Order', 'lines', 'foreignlink', 'lines'); // 2A

		$this->testAnnotation('map object single response', 'Order', 'salesmen', 'foreignlink', 'salesman'); // 3A
		$this->testAnnotation('map myself single response', 'Item', 'cross_selling', 'foreignlink', 'cross_selling'); // 3B
		$this->testAnnotation('map response', 'Shop', 'categories', 'foreignlink', 'category'); // 3C
		$this->testAnnotation('map response', 'Category', 'shops', 'foreignlink', 'shop'); // 3C
		$this->testAnnotation('map myself response', 'Category', 'super_categories', 'foreignlink', 'super_category'); // 3D
		$this->testAnnotation('map myself response', 'Category', 'sub_categories', 'foreignlink', 'sub_category'); // 3D
		$this->testAnnotation('map source concurrence no reponse', 'Item', 'secondary_categories', 'foreignlink', 'secondary_category'); // 3E
		$this->testAnnotation('map component no response', 'Best_Line', 'lines', 'foreignlink', 'line'); // 3F
		$this->testAnnotation('map component response', 'Item', 'lines', 'foreignlink', 'line'); // 3G
	}

}
