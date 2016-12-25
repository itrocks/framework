<?php
namespace ITRocks\Framework\Reflection\Annotation;

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

/**
 * Mapping annotations tests
 */
class Tests extends Test
{

	//------------------------------------------------------------------------------ $annotation_name
	/**
	 * @var string
	 */
	private $annotation_name;

	//-------------------------------------------------------------------------------- $assumed_class
	/**
	 * @var string
	 */
	private $assumed_class;

	//------------------------------------------------------------------------- $multiple_annotations
	/**
	 * @var boolean
	 */
	private $multiple_annotations = false;

	//-------------------------------------------------------------------------------- testAnnotation
	/**
	 * @param $description     string
	 * @param $class_name      string
	 * @param $property_name   string
	 * @param $assumed_value   mixed
	 * @param $annotation_name string
	 * @param $assumed_class   string
	 */
	private function testAnnotation(
		$description, $class_name, $property_name, $assumed_value, $annotation_name = null,
		$assumed_class = null
	) {
		if (!isset($annotation_name)) {
			$annotation_name = $this->annotation_name;
		}
		if (!isset($assumed_class) && isset($this->assumed_class)) {
			$assumed_class = $this->assumed_class;
		}
		$property = new Reflection_Property($class_name, $property_name);
		$annotation = $property->getAnnotation($annotation_name);
		$this->assume(
			$class_name . DOT . $property_name . AT . $annotation_name . SP . DQ . $assumed_value . DQ
				. SP . '(' . $description . ')',
			($assumed_class ? (get_class($annotation) . ':') : '') . $annotation->value,
			($assumed_class ? ($assumed_class . ':') : '') . $assumed_value
		);
	}

	//------------------------------------------------------------------------------- testBeforeWrite
	public function testBeforeWrite()
	{
		$this->method('@before_write');

		$this->assumed_class = Method_Annotation::class;
		$this->multiple_annotations = true;
		$this->testClassAnnotation(
			'parsed', Item::class, Item::class . '::beforeWrite', 'before_write'
		);
		$this->testClassAnnotation(
			'cached', Item::class, Item::class . '::beforeWrite', 'before_write'
		);

		$this->multiple_annotations = false;
	}

	//--------------------------------------------------------------------------- testClassAnnotation
	/**
	 * @param $description     string
	 * @param $class_name      string
	 * @param $assumed_value   mixed
	 * @param $annotation_name string
	 * @param $assumed_class   string
	 */
	private function testClassAnnotation(
		$description, $class_name, $assumed_value, $annotation_name = null, $assumed_class = null
	) {
		if (!isset($annotation_name)) {
			$annotation_name = $this->annotation_name;
		}
		if (!isset($assumed_class) && isset($this->assumed_class)) {
			$assumed_class = $this->assumed_class;
		}
		$class = new Reflection_Class($class_name);
		$annotation = $this->multiple_annotations
			? $class->getAnnotations($annotation_name)[0]
			: $class->getAnnotation($annotation_name);
		$this->assume(
			$class_name . AT . $annotation_name . SP . DQ . $assumed_value . DQ
			. SP . '(' . $description . ')',
			($assumed_class ? (get_class($annotation) . ':') : '') . $annotation->value,
			($assumed_class ? ($assumed_class . ':') : '') . $assumed_value
		);
	}

	//----------------------------------------------------------------------------------- testForeign
	public function testForeign()
	{
		$this->method('@foreign');

		$this->annotation_name = Foreign_Annotation::ANNOTATION;
		$this->assumed_class   = Foreign_Annotation::class;

		$this->testAnnotation('object', Order_Line::class, 'client', null); // 1A
		$this->testAnnotation('object myself', Client::class, 'client', null); // 1B
		$this->testAnnotation('object concurrent foreign 1', Order::class, 'client', null); // 1C
		$this->testAnnotation('object concurrent foreign 2', Order::class, 'delivery_client', null); // 1C
		$this->testAnnotation('object from component', Order_Line::class, 'order', 'lines'); // 1D
		$this->testAnnotation('same master object', Item::class, 'main_category', null); // 1E
		$this->testAnnotation('myself concurrent object', Category::class, 'main_super_category', null); // 1F

		$this->testAnnotation('simple collection', Order::class, 'lines', 'order'); // 2A

		$this->testAnnotation('map object single response', Order::class, 'salesmen', 'order'); // 3A
		$this->testAnnotation('map myself single response', Item::class, 'cross_selling', 'item'); // 3B
		$this->testAnnotation('map response', Shop::class, 'categories', 'shops'); // 3C
		$this->testAnnotation('map response', Category::class, 'shops', 'categories'); // 3C
		$this->testAnnotation('map myself response', Category::class, 'super_categories', 'sub_categories'); // 3D
		$this->testAnnotation('map myself response', Category::class, 'sub_categories', 'super_categories'); // 3D
		$this->testAnnotation('map source concurrence no response', Item::class, 'secondary_categories', 'item'); // 3E
		$this->testAnnotation('map component no response', Best_Line::class, 'lines', 'best_line'); // 3F
		$this->testAnnotation('map component response', Item::class, 'lines', 'item'); // 3G
	}

	//------------------------------------------------------------------------------- testForeignlink
	public function testForeignlink()
	{
		$this->method('@foreignlink');

		$this->annotation_name = 'foreignlink';
		$this->assumed_class   = Foreignlink_Annotation::class;

		$this->testAnnotation('object', Order_Line::class, 'client', 'client'); // 1A
		$this->testAnnotation('object myself', Client::class, 'client', 'client'); // 1B
		$this->testAnnotation('object concurrent foreign 1', Order::class, 'client', 'client'); // 1C
		$this->testAnnotation('object concurrent foreign 2', Order::class, 'delivery_client', 'delivery_client'); // 1C
		$this->testAnnotation('object from component', Order_Line::class, 'order', 'order'); // 1D
		$this->testAnnotation('same master object', Item::class, 'main_category', 'main_category'); // 1E
		$this->testAnnotation('myself concurrent object', Category::class, 'main_super_category', 'main_super_category'); // 1F

		$this->testAnnotation('simple collection', Order::class, 'lines', 'lines'); // 2A

		$this->testAnnotation('map object single response', Order::class, 'salesmen', 'salesman'); // 3A
		$this->testAnnotation('map myself single response', Item::class, 'cross_selling', 'cross_selling'); // 3B
		$this->testAnnotation('map response', Shop::class, 'categories', 'category'); // 3C
		$this->testAnnotation('map response', Category::class, 'shops', 'shop'); // 3C
		$this->testAnnotation('map myself response', Category::class, 'super_categories', 'super_category'); // 3D
		$this->testAnnotation('map myself response', Category::class, 'sub_categories', 'sub_category'); // 3D
		$this->testAnnotation('map source concurrence no response', Item::class, 'secondary_categories', 'secondary_category'); // 3E
		$this->testAnnotation('map component no response', Best_Line::class, 'lines', 'line'); // 3F
		$this->testAnnotation('map component response', Item::class, 'lines', 'line'); // 3G
	}

}
