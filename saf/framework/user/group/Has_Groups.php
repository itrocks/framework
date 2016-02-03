<?php
namespace SAF\Framework\User\Group;

use SAF\Framework\Builder;
use SAF\Framework\Controller;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Session;
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

	//--------------------------------------------------------------------------- getLowLevelFeatures
	/**
	 * Load user groups low-level features
	 *
	 * @return Low_Level_Feature[]
	 */
	private function getLowLevelFeatures()
	{
		$features = [];
		foreach ($this->groups as $group) {
			$features = array_merge($features, $group->getLowLevelFeatures());
		}
		return $features;
	}

	//----------------------------------------------------------------- lowLevelFeaturesToSearchArray
	/**
	 * Change low-level features to an array for fast-search
	 *
	 * @param $features Low_Level_Feature[]
	 * @return array
	 */
	private function lowLevelFeaturesToSearchArray($features)
	{
		$array = [];
		foreach ($features as $feature) {
			$uri = new Uri(SL . $feature->feature);
			$array[$uri->controller_name][$uri->feature_name] = $feature->options;
		}
		return $array;
	}

	//----------------------------------------------------------------------------------- hasAccessTo
	/**
	 * Returns true if the user has access to the $uri
	 * $uri is checked into path
	 *
	 * @param $uri string
	 * @return boolean
	 */
	public function hasAccessTo($uri)
	{
		//echo '- has access to ' . $uri . ' ?';
		/** @var $cache Low_Level_Features_Cache */
		$cache = Session::current()->get(Low_Level_Features_Cache::class, function() {
			return new Low_Level_Features_Cache(
				$this->lowLevelFeaturesToSearchArray($this->getLowLevelFeatures())
			);
		});
		$features = $cache->features;
		$uri = new Uri($uri);
		$class_name = Builder::current()->sourceClassName(
			Names::setToClass($uri->controller_name, false)
		);
		$feature_name = $uri->feature_name;
		$has_access =
			(isset($features[User::class]) && isset($features[User::class][Controller\Feature::F_SUPER]))
			|| (isset($features[$class_name]) && isset($features[$class_name][$feature_name]));
		//echo SP . $has_access . BR;
		return $has_access;
	}

}
