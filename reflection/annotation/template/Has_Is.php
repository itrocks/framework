<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;

/**
 * Has is() for annotations which values are delimited (constants)
 *
 * @extends Annotation
 */
trait Has_Is
{

	//-------------------------------------------------------------------------------------------- is
	/**
	 * @param $array string|string[]
	 * @return boolean
	 */
	public function is(array|string $array) : bool
	{
		/** @var $this Annotation|self */
		return in_array($this->value, is_array($array) ? $array : func_get_args());
	}

}
