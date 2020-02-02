<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\User\Group\Feature;
use ITRocks\Framework\User\Group\Has_Groups;
use ITRocks\Framework\User\Group\Low_Level_Feature;

/**
 * User group
 *
 * Used by access control plugins to manage the users access
 *
 * @business
 * @display_order name, groups, features
 * @feature
 * @override groups @foreignlink super_group
 */
class Group
{
	use Has_Groups { getLowLevelFeatures as private hasGroupsGetLowLevelFeatures; }
	use Has_Name;

	//------------------------------------------------------------------------------------- $features
	/**
	 * Each link to a feature is stored into the data-link as two strings : its name and path
	 *
	 * @link Map
	 * @var Feature[]
	 */
	public $features;

	//--------------------------------------------------------------------------- addLowLevelFeatures
	/**
	 * Add new features to an existing list of features
	 *
	 * If a feature already exists : accumulate options
	 *
	 * @param $features     Low_Level_Feature[] existing features (list to grow)
	 * @param $new_features Low_Level_Feature[] added features
	 */
	private function addLowLevelFeatures(array &$features, array $new_features)
	{
		foreach ($new_features as $path => $new_feature) {
			if (isset($features[$path])) {
				$old_feature = $features[$path];
				foreach ($new_feature->options as $key => $option) {
					$old_feature->options[$key] = $option;
				}
			}
			else {
				$features[$path] = $new_feature;
			}
		}
	}

	//--------------------------------------------------------------------------- getLowLevelFeatures
	/**
	 * Gets all features from $this->includes + $this->features
	 *
	 * @return Low_Level_Feature[]
	 */
	public function getLowLevelFeatures()
	{
		/** @var $features Low_Level_Feature[] */
		$features = [];
		// included groups first
		foreach ($this->groups as $group) {
			$this->addLowLevelFeatures($features, $group->getLowLevelFeatures());
		}
		// then features
		foreach ($this->features as $feature) {
			$this->addLowLevelFeatures($features, $feature->getAllFeatures());
		}
		return $features;
	}

}
