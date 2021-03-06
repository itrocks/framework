<?php
namespace ITRocks\Framework\Plugin\Installable\Installed;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Installable\Installed;
use ITRocks\Framework\RAD\Feature;

/**
 * A feature installed as a dependency of another features
 *
 * @store_name installed_dependencies
 */
class Dependency extends Installed
{

	//----------------------------------------------------------------------------------- $dependency
	/**
	 * @link Object
	 * @var Feature
	 */
	public $dependency;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $dependency string|Feature
	 * @return static
	 */
	public function add($dependency)
	{
		if (is_string($dependency)) {
			$dependency = Dao::searchOne(['plugin_class_name' => $dependency], Feature::class);
		}
		return $this->addProperties(['dependency' => $dependency]);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $dependency string|Feature
	 * @return static
	 */
	public function remove($dependency)
	{
		if (is_string($dependency)) {
			$dependency = Dao::searchOne(['plugin_class_name' => $dependency], Feature::class);
		}
		return $this->removeProperties(['dependency' => $dependency]);
	}

}
