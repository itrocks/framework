<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

/**
 * A very simple class, without AOP, to test @default simple and alone
 */
class Default_Simple
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @default defaultName
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------- defaultName
	/**
	 * @return string
	 */
	protected function defaultName()
	{
		return 'Robert';
	}

}
