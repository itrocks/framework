<?php
namespace SAF\Framework\View\Html\Builder;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Mapper;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\View;
use SAF\Framework\View\Html\Dom\Table;
use SAF\Framework\View\Html\Template;

/**
 * Takes a collection of objects and build an HTML output containing their data
 *
 * This is specifically for multiple objects of different classes extending the same abstract parent
 * class.
 */
class Abstract_Collection
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//----------------------------------------------------------------------------------- $collection
	/**
	 * @var object[]
	 */
	public $collection;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property   Reflection_Property
	 * @param $collection object[]
	 */
	public function __construct(Reflection_Property $property, $collection)
	{
		$this->class_name = $property->getType()->getElementTypeAsString();
		$this->collection = $collection;
		$this->property   = $property;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$result = '';
		foreach ($this->collection as $object) {
			$parameters = [$object, Parameter::IS_INCLUDED => true, Template::TEMPLATE => 'object'];
			$result .= View::run($parameters, [], [], get_class($object), 'output');
		}
		return $result;
	}

}
