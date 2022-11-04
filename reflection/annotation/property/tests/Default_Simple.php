<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Tools\Date_Time;

/**
 * A very simple class, without AOP, to test @default simple and alone
 *
 * @override age @default Default_Simple::defaultAge
 * @override null_age @default defaultAge
 */
class Default_Simple extends Default_Extended
{

	//---------------------------------------------------------------------------------- $alive_until
	/**
	 * @default Date_Time::max
	 * @link DateTime
	 * @var Date_Time|string
	 */
	public Date_Time|string $alive_until;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @default defaultName
	 * @var string
	 */
	public string $name;

	//-------------------------------------------------------------------------------------- $surname
	/**
	 * @var string
	 */
	public string $surname = 'Mitchum';

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
