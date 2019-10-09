<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Session;

/**
 * Post installation processes : have to be called after a plugin has been installed (on next page)
 *
 * - calculates new traits that have properties with @install calculate
 */
class Post
{

	//--------------------------------------------------------------------------- $install_properties
	/**
	 * @var array string[][] $install[$class_name][$property_name]
	 */
	public $install_properties = [];

	//------------------------------------------------------------------------------------------- get
	/**
	 * @param $default boolean
	 * @return Post
	 */
	public static function get($default = true)
	{
		return Session::current()->get(static::class, $default);
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * Apply post-install calculations
	 *
	 * @param $reset_when_done boolean
	 */
	public function install($reset_when_done = true)
	{
		$this->installProperties($reset_when_done);

		if ($reset_when_done) {
			Session::current()->remove($this);
		}
	}

	//----------------------------------------------------------------------------- installProperties
	/**
	 * Apply calculation on installed properties
	 *
	 * @param $reset_when_done boolean
	 */
	public function installProperties($reset_when_done = true)
	{
		foreach ($this->install_properties as $class_name => $properties) {
			Dao::begin();
			foreach (Dao::readAll($class_name) as $object) {
				foreach ($properties as $property_name => $install) {
					if ($install === 'calculate') {
						$object->$property_name;
					}
				}
				Dao::write($object, Dao::only(array_keys($properties)));
			}
			Dao::commit();
		}
		if ($reset_when_done) {
			$this->install_properties = [];
		}
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean
	 */
	public function isEmpty()
	{
		return !$this->install_properties;
	}

	//------------------------------------------------------------------------- willInstallProperties
	/**
	 * Scan trait for properties, store those that have an install annotation for post-installation
	 * process.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $trait_name string
	 */
	public function willInstallProperties($class_name, $trait_name)
	{
		$properties = [];
		/** @noinspection PhpUnhandledExceptionInspection trait must be valid */
		foreach ((new Reflection_Class($trait_name))->getProperties() as $property) {
			if ($install = $property->getAnnotation('install')->value) {
				$properties[$class_name][$property->name] = $install;
			}
		}
		if (!$properties) {
			return;
		}
		$this->install_properties = arrayMergeRecursive($this->install_properties, $properties);
	}

}
