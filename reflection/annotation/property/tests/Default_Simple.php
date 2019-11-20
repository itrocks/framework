<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

/**
 * A very simple class, without AOP, to test @default simple and alone
 *
 * @override age @default Default_Simple::defaultAge
 * @override null_age @default Default_Simple::defaultAge
 */
class Default_Simple extends Default_Extended
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @default defaultName
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $surname
	/**
	 * @var string
	 */
	public $surname = 'Mitchum';

	//------------------------------------------------------------------------------------ defaultAge
	/**
	 * @return integer
	 */
	protected function defaultAge()
	{
		return 43;
	}

	//----------------------------------------------------------------------------------- defaultName
	/**
	 * @return string
	 * @return_constant
	 */
	protected function defaultName()
	{
		return 'Robert';
	}

}
