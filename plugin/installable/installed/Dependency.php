<?php
namespace ITRocks\Framework\Plugin\Installable\Installed;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Installable\Installed;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * A feature installed as a dependency of another features
 */
#[Store('installed_dependencies')]
class Dependency extends Installed
{

	//----------------------------------------------------------------------------------- $dependency
	public Feature $dependency;

	//------------------------------------------------------------------------------------------- add
	public function add(string|Feature $dependency) : static
	{
		if (is_string($dependency)) {
			$dependency = Dao::searchOne(['plugin_class_name' => $dependency], Feature::class);
		}
		return $this->addProperties(['dependency' => $dependency]);
	}

	//---------------------------------------------------------------------------------------- remove
	public function remove(string|Feature $dependency) : static
	{
		if (is_string($dependency)) {
			$dependency = Dao::searchOne(['plugin_class_name' => $dependency], Feature::class);
		}
		return $this->removeProperties(['dependency' => $dependency]);
	}

}
