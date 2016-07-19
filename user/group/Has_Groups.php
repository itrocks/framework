<?php
namespace SAF\Framework\User\Group;

use SAF\Framework\Builder;
use SAF\Framework\Controller;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Tools\Names;
use SAF\Framework\User;
use SAF\Framework\User\Group;

/**
 * For business objects that need groups.
 *
 * Done for User, but can be used for other environments objects : eg organisations, etc.
 *
 * @business
 */
trait Has_Groups
{

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @link Map
	 * @var Group[]
	 */
	public $groups;

	//------------------------------------------------------------------------------ getAccessOptions
	/**
	 * Returns the options of an access, or null if the user has no access to this path
	 *
	 * @param $uri string The uri (path of the feature)
	 * @return array|null If the object has access to the $uri, returns the active options. Else null.
	 * Beware : the returned array may be null if the user has the access but if there are no options.
	 */
	public function getAccessOptions($uri)
	{
		$cache = Low_Level_Features_Cache::current();
		$features = $cache->features;
		$uri = new Uri($uri);
		$class_name = Builder::current()->sourceClassName(
			Names::setToClass($uri->controller_name, false)
		);
		$feature_name = $uri->feature_name;
		if (isset($features[$class_name]) && isset($features[$class_name][$feature_name])) {
			return $features[$class_name][$feature_name];
		}
		if (
			isset($features[User::class]) && isset($features[User::class][Controller\Feature::F_SUPER])
		) {
			return $features[User::class][Controller\Feature::F_SUPER];
		}
		return null;
	}

	//--------------------------------------------------------------------------- getLowLevelFeatures
	/**
	 * Load user groups low-level features
	 *
	 * @return Low_Level_Feature[]
	 */
	public function getLowLevelFeatures()
	{
		$features = [];
		foreach ($this->groups as $group) {
			$features = array_merge($features, $group->getLowLevelFeatures());
		}
		return $features;
	}

	//----------------------------------------------------------------------------------- hasAccessTo
	/**
	 * Returns true if the user has access to the $uri
	 * $uri is checked into path
	 *
	 * @param $uri string
	 * @return boolean Returns true if the user has access to this uri
	 */
	public function hasAccessTo($uri)
	{
		return !is_null($this->getAccessOptions($uri));
	}

}
