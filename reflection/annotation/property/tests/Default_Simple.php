<?php
namespace ITRocks\Framework\Reflection\Annotation\Property\Tests;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A very simple class, without AOP, to test #Default simple and alone
 */
#[Override('age',      new Default_('defaultAge'))]
#[Override('null_age', new Default_('defaultAge'))]
#[Store]
class Default_Simple extends Default_Extended
{

	//---------------------------------------------------------------------------------- $alive_until
	#[Default_([Date_Time::class, 'max'])]
	public Date_Time|string $alive_until;

	//----------------------------------------------------------------------------------------- $name
	#[Default_('defaultName')]
	public string $name;

	//-------------------------------------------------------------------------------------- $surname
	public string $surname = 'Mitchum';

	//------------------------------------------------------------------------------------ defaultAge
	/** @noinspection PhpUnused #Default */
	public static function defaultAge() : int
	{
		return 43;
	}

	//----------------------------------------------------------------------------------- defaultName
	/** @return_constant */
	public function defaultName() : string
	{
		return 'Robert';
	}

}
