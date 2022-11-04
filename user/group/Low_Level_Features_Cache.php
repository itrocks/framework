<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\User;

/**
 * Low level features cache to keep into session / file / anywhere you want
 */
class Low_Level_Features_Cache
{
	use Current { current as pCurrent; }

	//------------------------------------------------------------------------------------- $features
	/**
	 * @var Low_Level_Feature[]
	 */
	public array $features;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $features Low_Level_Feature[]
	 */
	public function __construct(array $features = null)
	{
		if (isset($features)) {
			$this->features = $features;
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current static|null
	 * @return ?static
	 */
	public static function current(self $set_current = null) : ?static
	{
		/** @var $current static */
		$current = self::pCurrent($set_current);
		if (!isset($current)) {
			$user = User::current();
			if (isA($user, Has_Groups::class)) {
				/** @var $user User|Has_Groups */
				$current = self::pCurrent(new Low_Level_Features_Cache(
					self::lowLevelFeaturesToSearchArray($user->getLowLevelFeatures())
				));
			}
		}
		return $current;
	}

	//----------------------------------------------------------------- lowLevelFeaturesToSearchArray
	/**
	 * Change low-level features to an array for fast-search
	 *
	 * @param $features Low_Level_Feature[]
	 * @return array
	 */
	private static function lowLevelFeaturesToSearchArray(array $features) : array
	{
		$array = [];
		foreach ($features as $feature) {
			$uri = new Uri(SL . $feature->feature);
			$array[$uri->controller_name][$uri->feature_name] = $feature->options;
		}
		return $array;
	}

}
