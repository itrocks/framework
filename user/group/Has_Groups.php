<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Group;

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
	public array $groups;

	//------------------------------------------------------------------------------ getAccessOptions
	/**
	 * Returns the options of an access, or null if the user has no access to this path
	 *
	 * @param $uri string The uri (path of the feature)
	 * @return ?array If the object has access to the $uri, returns the active options. Else null.
	 * Beware : the returned array may be [] if the user has the access but if there are no options.
	 */
	public function getAccessOptions(string $uri) : ?array
	{
		$cache    = Low_Level_Features_Cache::current();
		$features = $cache ? $cache->features : [];
		$uri      = new Uri($uri);
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
	public function getLowLevelFeatures() : array
	{
		$features = [];
		foreach ($this->groups as $group) {
			$features = array_merge($features, $group->getLowLevelFeatures());
		}
		return $features;
	}

	//----------------------------------------------------------------------------------- hasAccessTo
	/**
	 * Returns true if the user has access to the $uri.
	 * $uri is checked into path.
	 *
	 * @param $uri ?string
	 * @return boolean Returns true if the user has access to this uri
	 */
	public function hasAccessTo(?string $uri) : bool
	{
		return !$uri || !is_null($this->getAccessOptions($uri));
	}

	//-------------------------------------------------------------------------------------- hasGroup
	/**
	 * Check if object has the given group
	 *
	 * @param $group Group|integer|string
	 * @return boolean
	 */
	public function hasGroup(Group|int|string $group) : bool
	{
		$identifier = null;
		// Group
		if ($group instanceof Group) {
			$identifier = Dao::getObjectIdentifier($group);
		}
		// string : name of the group
		elseif (is_string($group)) {
			$group = Dao::searchOne(['name' => $group], Group::class);
			if ($group) {
				$identifier = Dao::getObjectIdentifier($group);
			}
		}
		// integer : identifier
		elseif (isStrictNumeric($group, false, false)) {
			$identifier = (integer)$group;
		}

		if (!$identifier) {
			return false;
		}

		foreach($this->groups as $object_group) {
			if (
				(Dao::getObjectIdentifier($object_group) == $identifier)
				|| $object_group->hasGroup($identifier)
			) {
				return true;
			}
		}

		return false;
	}

}
