<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\Tools\Call_Stack;

/**
 * Common code for installed things
 *
 * @business
 */
abstract class Installed
{

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * The feature, context for the installation
	 *
	 * Automatically set to the feature that matches the nearest 'plugin_class_name' argument when
	 * the constructor is called.
	 *
	 * @store false
	 * @var Feature
	 */
	protected $feature;

	//------------------------------------------------------------------------------------- $features
	/**
	 * @link Map
	 * @set_store_name {master}_features
	 * @var Feature[]
	 */
	public $features;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Installed constructor.
	 *
	 * @param $feature_plugin Feature|Installable|string
	 */
	public function __construct($feature_plugin = null)
	{
		$this->feature = ($feature_plugin instanceof Feature)
			? $feature_plugin
			: $this->pluginClassNameFeature($feature_plugin);
	}

	//--------------------------------------------------------------------------------- addProperties
	/**
	 * @param $property_values mixed[]
	 * @return static installed element after count increment (1..n)
	 */
	protected function addProperties(array $property_values)
	{
		$installed = Dao::searchOne($property_values, static::class);

		if ($installed) {
			$already_installed = false;
			foreach ($this->features as $installed_feature) {
				if (Dao::is($installed_feature, $this->feature)) {
					$already_installed = true;
					break;
				}
			}
			if (!$already_installed) {
				$installed->features[] = $this->feature;
				Dao::write($installed, Dao::only('features'));
			}
		}

		else {
			$installed = Builder::create(static::class, [$this->feature]);
			foreach ($property_values as $property_name => $value) {
				$installed->$property_name = $value;
			}
			$installed->features = [$this->feature];
			Dao::write($installed);
		}

		return $installed;
	}

	//------------------------------------------------------------------------ pluginClassNameFeature
	/**
	 * Search for a $plugin_class_name parameter into the call stack, and get the matching RAD feature
	 *
	 * @param $plugin_class_name Installable|string if null, will get it from the call stack nearest argument
	 * @return Feature
	 */
	public function pluginClassNameFeature($plugin_class_name = null)
	{
		if (!$plugin_class_name) {
			$call_stack        = new Call_Stack();
			$plugin_class_name = $call_stack->getArgumentValue('plugin_class_name', true);
		}
		$plugin_class_name = is_object($plugin_class_name)
			? Builder::current()->sourceClassName(get_class($plugin_class_name))
			: $plugin_class_name;
		return Dao::searchOne(['plugin_class_name' => $plugin_class_name], Feature::class);
	}

	//------------------------------------------------------------------------------ removeProperties
	/**
	 * @param $property_values mixed[]
	 * @return static|null removed element after count decrement (0..n). Null of was not installed
	 */
	protected function removeProperties(array $property_values)
	{
		$property_values['features'] = [$this->feature];
		$installed = Dao::searchOne($property_values, static::class);

		if (!$installed) {
			return null;
		}

		$removed_feature = false;
		foreach ($installed->features as $feature_key => $installed_feature) {
			if (Dao::is($installed_feature, $this->feature)) {
				unset($installed->features[$feature_key]);
				$removed_feature = true;
			}
		}

		if ($installed->features) {
			if ($removed_feature) {
				Dao::write($installed, Dao::only('features'));
			}
		}
		else {
			Dao::delete($installed);
		}

		return $installed;
	}

}
