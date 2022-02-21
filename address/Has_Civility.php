<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;

/**
 * @extends Person
 * @feature Person with civility
 * @feature_install initCivilities
 * @see Person_Plugin
 */
trait Has_Civility
{

	//------------------------------------------------------------------------------------- $civility
	/**
	 * @link Object
	 * @var Civility
	 */
	public $civility;

	//-------------------------------------------------------------------------------- initCivilities
	/**
	 * Called when the civilities feature is installed
	 *
	 * @noinspection PhpUnused @feature_install
	 */
	public static function initCivilities()
	{
		if (!Dao::count(Civility::class)) {
			Dao::begin();
			foreach (['Mr' => 'mister', 'Mrs' => 'mistress'] as $code => $name) {
				$civility       = new Civility();
				$civility->code = Loc::tr($code);
				$civility->name = Loc::tr($name);
				Dao::write($civility);
			}
			Dao::commit();
		}
	}

}
