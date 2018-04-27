<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Set;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Implicit;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;

/**
 * Maintains the user features database
 */
class Maintainer implements Registerable, Updatable
{

	//-------------------------------------------------------------------------- applicationClassName
	/**
	 * @param $class_name string
	 * @return string
	 */
	protected function applicationClassName($class_name)
	{
		$namespace = lParse($class_name, BS, 2);
		if (!class_exists($namespace . BS . 'Application')) {
			$namespace = lParse($class_name, BS, 1);
		}
		return $namespace . BS . 'Application';
	}

	//------------------------------------------------------------------ featureAnnotationsToFeatures
	/**
	 * Scan all @feature class annotations which value start by an uppercase letter : these are
	 * features here to be installable too
	 *
	 * @return Feature[]
	 */
	protected function featureAnnotationsToFeatures()
	{
		$dependencies = Dao::search(['type' => Dependency::T_FEATURE], Dependency::class);
		$features     = [];
		foreach ($dependencies as $dependency) {
			$features[] = $this->pluginClassNameAndTitleToFeature(
				$dependency->class_name, $dependency->dependency_name
			);
		}
		return $features;
	}

	//------------------------------------------------------------------------- installableToFeatures
	/**
	 * Search all classes that implements Installable and write them as features you can install or
	 * uninstall
	 *
	 * @return Feature[]
	 */
	protected function installableToFeatures()
	{
		$dependencies = Dao::search(
			['dependency_name' => Installable::class, 'type' => Dependency::T_IMPLEMENTS],
			Dependency::class
		);
		$features = [];
		foreach ($dependencies as $dependency) {
			if (!is_a($dependency->class_name, Implicit::class, true)) {
				$features[] = $this->pluginClassNameToFeature($dependency->class_name);
			}
		}
		return $features;
	}

	//------------------------------------------------------------------- installableToFeaturesUpdate
	/**
	 * Update features list into main DAO data link
	 *
	 * @return Feature[]
	 */
	public function installableToFeaturesUpdate()
	{
		$features = array_merge($this->installableToFeatures(), $this->featureAnnotationsToFeatures());
		(new Set())->replace($features, Feature::class);
		return $features;
	}

	//-------------------------------------------------------------- pluginClassNameAndTitleToFeature
	/**
	 * @param $plugin_class_name string
	 * @param $title             string
	 * @return Feature
	 */
	protected function pluginClassNameAndTitleToFeature($plugin_class_name, $title)
	{
		$feature = Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class)
			?: new Feature($title);
		$feature->application_class_name = $this->applicationClassName($plugin_class_name);
		$feature->plugin_class_name      = $plugin_class_name;
		return $feature;
	}

	//---------------------------------------------------------------------- pluginClassNameToFeature
	/**
	 * @param $plugin_class_name string
	 * @return Feature
	 */
	protected function pluginClassNameToFeature($plugin_class_name)
	{
		$documentation = (new Reflection_Class($plugin_class_name))->getDocComment();
		$documentation = trim(str_replace(["/**\n", "\n * ", "\n *\n", "\n */"], LF, $documentation));

		$feature = Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class)
			?: new Feature();

		$feature->application_class_name = $this->applicationClassName($plugin_class_name);
		$feature->description            = trim(mParse($documentation, LF, LF . AT));
		$feature->plugin_class_name      = $plugin_class_name;
		$feature->title                  = lParse($documentation, LF);

		return $feature;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$application_updater = Application_Updater::get();
		$application_updater->addUpdatable($this);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer updates() executes a full update and does not care of it
	 */
	public function update($last_time)
	{
		$this->installableToFeaturesUpdate();
	}

}