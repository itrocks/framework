<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * @feature Person with civility
 * @feature_install initCivilities
 * @see Person_Plugin
 */
#[Extend(Person::class)]
trait Has_Civility
{

	//------------------------------------------------------------------------------------- $civility
	public ?Civility $civility;

	//-------------------------------------------------------------------------------- initCivilities
	/**
	 * Called when the civilities feature is installed
	 *
	 * @noinspection PhpUnused @feature_install
	 */
	public static function initCivilities() : void
	{
		if (Dao::count(Civility::class)) {
			return;
		}
		Dao::begin();
		foreach (['Mr' => 'mister', 'Mrs' => 'mistress'] as $code => $name) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$civility       = Builder::create(Civility::class);
			$civility->code = Loc::tr($code);
			$civility->name = Loc::tr($name);
			Dao::write($civility);
		}
		Dao::commit();
	}

}
