<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;

/**
 * Has is() for annotations which values are delimited (constants)
 */
#[Extends_(Annotation::class)]
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
