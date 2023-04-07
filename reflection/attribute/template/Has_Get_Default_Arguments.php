<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection;

interface Has_Get_Default_Arguments
{

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments(Reflection $reflection) : array;

}
