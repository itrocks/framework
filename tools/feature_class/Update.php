<?php
namespace ITRocks\Framework\Tools\Feature_Class;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;

/**
 * Update the @feature class storage in main data list
 *
 * This works like a cache : we scan all @feature classes, remove those that are not available
 * any more, and add new ones
 */
class Update implements Registerable, Updatable
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		Application_Updater::get()->addUpdatable($this);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Update feature classes cache
	 *
	 * @param $last_time integer
	 */
	public function update($last_time)
	{
		[$class_names, $feature_classes, $write] = $this->updateInit();
		foreach (Dao::search(['type' => Dependency::T_DECLARATION], Dependency::class) as $dependency) {
			$class_name = Builder::className($dependency->class_name);
			$this->updateClassName($class_name, $class_names, $feature_clases, $write);
		}
		$this->writeFeatureClasses($feature_classes, $write);
	}

	//------------------------------------------------------------------------------- updateClassName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name      string
	 * @param $class_names     string[]
	 * @param $feature_classes Keep[]
	 * @param $write           Keep[]
	 */
	protected function updateClassName(
		$class_name, array &$class_names, array &$feature_classes, array &$write
	) {
		if (isset($class_names[$class_name]) || !class_exists($class_name)) {
			return;
		}
		/** @noinspection PhpUnhandledExceptionInspection class_exists */
		$class = new Reflection_Class($class_name);
		if ($class->isAbstract()) {
			return;
		}
		$feature = null;
		foreach ($class->getAnnotations('feature') as $feature_annotation) {
			$feature = $feature_annotation->value;
			if ($feature === 'false') {
				$feature = false;
			}
			if ($feature === 'true') {
				$feature = true;
			}
			if (is_bool($feature) || ctype_lower(substr($feature, 0, 1))) {
				break;
			}
		}
		if (($feature === true) || ctype_lower(substr($feature, 0, 1))) {
			$class_name = Builder::current()->sourceClassName($class_name);
			$name       = Display_Annotation::of($class)->value;
			// update name
			if (isset($feature_classes[$class_name])) {
				$feature_class       = $feature_classes[$class_name];
				$feature_class->keep = true;
				if ($feature_class->name !== $name) {
					$feature_class->name = $name;
					$write[$class_name]  = $feature_class;
				}
			}
			// next feature class
			else {
				$feature_class                = new Keep($class_name, $name);
				$feature_class->keep          = true;
				$feature_classes[$class_name] = $feature_class;
				$write[$class_name]           = $feature_class;
			}
		}
	}

	//------------------------------------------------------------------------------------ updateInit
	/**
	 * @return array
	 */
	protected function updateInit()
	{
		$feature_classes = Dao::readAll(Keep::class, Dao::key('class_name'));
		$class_names     = [];
		$write           = [];
		return [$class_names, $feature_classes, $write];
	}

	//--------------------------------------------------------------------------- writeFeatureClasses
	/**
	 * @param $feature_classes string[]
	 * @param $write           Keep[]
	 */
	protected function writeFeatureClasses(array $feature_classes, array $write)
	{
		Dao::begin();
		foreach ($feature_classes as $feature_class) {
			if (!$feature_class->keep) {
				Dao::delete($feature_class);
			}
		}
		foreach ($write as $feature_class) {
			Dao::write($feature_class);
		}
		Dao::commit();
	}

}
