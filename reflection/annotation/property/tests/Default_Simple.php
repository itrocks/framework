<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A very simple class, without AOP, to test @default simple and alone
 *
 * @override age @default Default_Simple::defaultAge
 * @override null_age @default defaultAge
 */
#[Store]
class Default_Simple extends Default_Extended
{

	//---------------------------------------------------------------------------------- $alive_until
	/**
	 * @default Date_Time::max
	 */
	public Date_Time|string $alive_until;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @default defaultName
	 */
	public string $name;

	//-------------------------------------------------------------------------------------- $surname
	public string $surname = 'Mitchum';

	//------------------------------------------------------------------------------------ defaultAge
	/**
	 * @noinspection PhpUnused @default
	 */
	public static function defaultAge() : int
	{
		return 43;
	}

	//----------------------------------------------------------------------------------- defaultName
	/**
	 * @return_constant
	 */
	public function defaultName() : string
	{
		return 'Robert';
	}

}
