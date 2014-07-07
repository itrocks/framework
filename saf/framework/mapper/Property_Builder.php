<?php
namespace SAF\Framework\Mapper;

use SAF\Framework\Reflection\Reflection_Property;

/**
 * The property builder is the interface for all specific properties builder set by @edit
 */
interface Property_Builder
{

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return mixed
	 */
	public function buildValue($object, $null_if_empty);

}
