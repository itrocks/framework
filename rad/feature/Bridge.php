<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\RAD\Feature;

/**
 * Bridge feature
 *
 * When a feature is installed, this :
 *
 * - get the bridge features associated to this feature
 * - if all features of the bridge feature are installed : automatically install this bridge feature
 */
class Bridge
{

	//------------------------------------------------------------------------------------ $installer
	/**
	 * @var Installer
	 */
	public $installer;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $installer Installer
	 */
	public function __construct(Installer $installer)
	{
		$this->installer = $installer;
	}

	//--------------------------------------------------------------------------- automaticInstallFor
	/**
	 * Automatically install bridge features for the installed feature class
	 *
	 * @param $feature_class string The already installed feature class
	 */
	public function automaticInstallFor($feature_class)
	{
		foreach ($this->bridgeFeatures($feature_class) as $bridge_feature) {
			if (!$this->isInstalled($bridge_feature)) {
				foreach ($this->featureClasses($bridge_feature) as $bridge_group) {
					if ($this->isGroupInstalled($bridge_group)) {
						$this->install($bridge_feature);
						break;
					}
				}
			}
		}
	}

	//------------------------------------------------------------------------- automaticUninstallFor
	/**
	 * Automatically uninstall bridge features for the uninstalled feature class
	 *
	 * @param $feature_class string The already uninstalled feature class
	 */
	public function automaticUninstallFor($feature_class)
	{
		foreach ($this->bridgeFeatures($feature_class) as $bridge_feature) {
			if ($this->isInstalled($bridge_feature)) {
				$installed_groups = 0;
				foreach ($this->featureClasses($bridge_feature) as $bridge_group) {
					if ($this->isGroupInstalled($bridge_group)) {
						$installed_groups ++;
					}
				}
				if (!$installed_groups) {
					$this->uninstall($bridge_feature);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- bridgeFeatures
	/**
	 * @param $feature_class string
	 * @return string[]
	 */
	protected function bridgeFeatures($feature_class)
	{
		$bridge_features = [];
		$dependencies = Dao::search(
		['dependency_name' => $feature_class, 'type' => Dependency::T_BRIDGE_FEATURE],
		Dependency::class
		);
		foreach ($dependencies as $dependency) {
			$bridge_features[$dependency->class_name] = $dependency->class_name;
		}
		return $bridge_features;
	}

	//-------------------------------------------------------------------------------- featureClasses
	/**
	 * @param $bridge_feature string
	 * @return array string[integer $group][integer $key]
	 */
	protected function featureClasses($bridge_feature)
	{
		$feature_classes = [];
		$dependencies = Dao::search(
		['class_name' => $bridge_feature, 'type' => Dependency::T_BRIDGE_FEATURE],
		Dependency::class
		);
		foreach ($dependencies as $dependency) {
			$feature_classes[$dependency->line][] = $dependency->dependency_name;
		}
		return $feature_classes;
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @param $feature_name string
	 */
	protected function install($feature_name)
	{
		$this->installer->install($feature_name);
	}

	//------------------------------------------------------------------------------ isGroupInstalled
	/**
	 * @param $bridge_group string[]
	 * @return boolean true if all features of the group are installed
	 */
	protected function isGroupInstalled(array $bridge_group)
	{
		foreach ($bridge_group as $feature) {
			if (!$this->isInstalled($feature)) {
				return false;
			}
		}
		return true;
	}

	//----------------------------------------------------------------------------------- isInstalled
	/**
	 * @param $feature_name string
	 * @return Feature|null set only if the feature is installed
	 */
	protected function isInstalled($feature_name)
	{
		return Dao::searchOne(
			['plugin_class_name' => $feature_name, 'status' => Status::INSTALLED],
			Feature::class
		);
	}

	//------------------------------------------------------------------------------------- uninstall
	/**
	 * @param $feature_name string
	 */
	protected function uninstall($feature_name)
	{
		$this->installer->uninstall($feature_name);
	}

}
