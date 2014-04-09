<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Multiple_Annotation;

/**
 * A @group annotation contains a name and several values, and is a multiple annotation too
 * It enable to group properties into named groups
 *
 * @example @group first group property_1 property_2 property_3
 * and then @group second group property_4 property_5
 * will create two annotations : one with the name 'first group' and each property name as values,
 * the second with the name 'second group' and each of its property name as string values.
 */
class Group_Annotation extends List_Annotation implements Multiple_Annotation
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The group name
	 *
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		$i = strpos($value, ',');
		if ($i === false) {
			$i = strlen($value);
		}
		$i = strrpos(substr($value, 0, $i), SP);
		if ($i === false) {
			$i = strlen($value);
		}
		$this->name = substr($value, 0, $i);
		parent::__construct(substr($value, $i + 1));
	}

}
