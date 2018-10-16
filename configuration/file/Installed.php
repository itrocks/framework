<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Dao;

/**
 * Common code for installed things
 */
abstract class Installed
{

	//---------------------------------------------------------------------------------------- $count
	/**
	 * how many times this thing has been installed
	 *
	 * Each time it is installed, this count is incremented
	 * Used for uninstall : this element will be uninstalled only if $count value is 0
	 *
	 * @var integer
	 */
	public $count;

	//--------------------------------------------------------------------------------- addProperties
	/**
	 * @param $property_values mixed[]
	 * @return static installed element after count increment (1..n)
	 */
	protected static function addProperties($property_values)
	{
		$installed = Dao::searchOne($property_values, static::class);
		if ($installed) {
			$installed ->count ++;
			Dao::write($installed, Dao::only('count'));
		}
		else {
			$installed = new static();
			foreach ($property_values as $property_name => $value) {
				$installed->$property_name = $value;
			}
			$installed->count = 1;
			Dao::write($installed);
		}
		return $installed;
	}

	//------------------------------------------------------------------------------ removeProperties
	/**
	 * @param $property_values mixed[]
	 * @return static|null removed element after count decrement (0..n). Null of was not installed
	 */
	protected static function removeProperties($property_values)
	{
		$installed = Dao::searchOne($property_values, static::class);
		if (!$installed) {
			return null;
		}
		$installed->count --;
		if ($installed->count) {
			Dao::write($installed, Dao::only('count'));
		}
		else {
			Dao::delete($installed);
		}
		return $installed;
	}

}
