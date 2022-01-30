<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Tools\Date_Time;

/**
 * A very simple class, without AOP, to test @default simple and alone
 *
 * @override age @default Default_Simple::defaultAge
 * @override null_age @default defaultAge
 * @see Default_Simple
 */
class Default_Simple extends Default_Extended
{

	//---------------------------------------------------------------------------------- $alive_until
	/**
	 * @default Date_Time::max
	 * @link DateTime
	 * @var Date_Time
	 */
	public $alive_until;

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
	public static function defaultAge() : int
	{
		return 43;
	}

	//----------------------------------------------------------------------------------- defaultName
	/**
	 * @return string
	 * @return_constant
	 */
	public function defaultName() : string
	{
		return 'Robert';
	}

}
