<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Set;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\Dependency\Declaration;
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
	protected function applicationClassName(string $class_name) : string
	{
		$namespace = lParse($class_name, BS, 2);
		if (!class_exists($namespace . BS . 'Application')) {
			$namespace = lParse($class_name, BS);
		}
		return $namespace . BS . 'Application';
	}

	//------------------------------------------------------------------ featureAnnotationsToFeatures
	/**
	 * Scan all class feature annotations which value start by an uppercase letter : these are
	 * features here to be installable too
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Feature[]
	 */
	protected function featureAnnotationsToFeatures() : array
	{
		$search = [
			'declaration' => [Declaration::BUILT_IN, Declaration::INSTALLABLE],
			'type'        => [Dependency::T_BRIDGE_FEATURE, Dependency::T_FEATURE]
		];
		$dependencies = Dao::search($search, Dependency::class, Dao::groupBy('class_name'));
		$features     = [];
		foreach ($dependencies as $dependency) {
			/** @noinspection PhpUnhandledExceptionInspection valid dependency */
			if (!(new Reflection_Class($dependency->class_name))->getAnnotation('feature_off')->value) {
				$feature = $this->pluginClassNameAndTitleToFeature(
					$dependency->class_name,
					$dependency->dependency_name,
					($dependency->type === Dependency::T_BRIDGE_FEATURE)
				);
				if ($dependency->declaration === Declaration::BUILT_IN) {
					$feature->status = Status::BUILT_IN;
				}
				$features[] = $feature;
			}
		}
		return $features;
	}

	//------------------------------------------------------------------------- installableToFeatures
	/**
	 * Search all classes that implements Installable and write them as features you can install or
	 * uninstall
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $abstract_installable_class_name string|null Installable is on the abstract class : get
	 *                                         sons
	 * @return Feature[]
	 */
	protected function installableToFeatures(string $abstract_installable_class_name = null) : array
	{
		$search = $abstract_installable_class_name
			? ['dependency_name' => $abstract_installable_class_name, 'type' => Dependency::T_EXTENDS]
			: ['dependency_name' => Installable::class, 'type' => Dependency::T_IMPLEMENTS];
		/** @var $dependencies Dependency[] */
		$dependencies = Dao::search($search, Dependency::class);
		$features     = [];
		foreach ($dependencies as $dependency) {
			/** @noinspection PhpUnhandledExceptionInspection valid dependency */
			if (
				!is_a($dependency->class_name, Implicit::class, true)
				&& class_exists($dependency->class_name)
				&& !(new Reflection_Class($dependency->class_name))->getAnnotation('feature_off')->value
			) {
				/** @noinspection PhpUnhandledExceptionInspection class_exists */
				if ((new Reflection_Class($dependency->class_name))->isAbstract()) {
					$features = array_merge($features, $this->installableToFeatures($dependency->class_name));
				}
				else {
					$features[] = $this->pluginClassNameToFeature($dependency->class_name);
				}
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
	public function installableToFeaturesUpdate() : array
	{
		Dao::createStorage(Module::class);
		Dao::createStorage(Feature::class);
		$features = array_merge($this->installableToFeatures(), $this->featureAnnotationsToFeatures());
		(new Set)->replace($features, Feature::class);
		foreach ($features as $feature) {
			Loc::tr($feature->title);
		}
		return $features;
	}

	//-------------------------------------------------------------- pluginClassNameAndTitleToFeature
	/**
	 * @param $plugin_class_name string
	 * @param $title             string
	 * @param $bridge            boolean
	 * @return Feature
	 */
	protected function pluginClassNameAndTitleToFeature(
		string $plugin_class_name, string $title, bool $bridge = false
	) : Feature
	{
		$feature = Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class)
			?: new Feature($title);
		$feature->application_class_name = $this->applicationClassName($plugin_class_name);
		$feature->bridge                 = $bridge;
		$feature->plugin_class_name      = $plugin_class_name;
		return $feature;
	}

	//---------------------------------------------------------------------- pluginClassNameToFeature
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $plugin_class_name string
	 * @return Feature
	 */
	protected function pluginClassNameToFeature(string $plugin_class_name) : Feature
	{
		/** @noinspection PhpUnhandledExceptionInspection $plugin_class_name must be valid */
		$documentation = (new Reflection_Class($plugin_class_name))->getDocComment();
		$documentation = trim(str_replace(["/**\n", "\n * ", "\n *\n", "\n */"], LF, $documentation));

		$feature = Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class)
			?: new Feature();

		$feature->application_class_name = $this->applicationClassName($plugin_class_name);
		$feature->description            = trim(rParse(lParse($documentation, LF . AT), LF));
		$feature->plugin_class_name      = $plugin_class_name;
		$feature->title                  = lParse($documentation, LF);

		return $feature;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		Application_Updater::get()->addUpdatable($this);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer updates() executes a full update and does not care of it
	 */
	public function update(int $last_time) : void
	{
		//$this->installableToFeaturesUpdate();
	}

}
