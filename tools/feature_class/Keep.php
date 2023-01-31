<?php
namespace ITRocks\Framework\Tools\Feature_Class;

use AllowDynamicProperties;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Feature_Class;

/**
 * Feature class with the 'done' information
 *
 * @todo Remove #AllowDynamicProperties where $id will be general to all #Store classes
 */
#[AllowDynamicProperties]
#[Store(false)]
class Keep extends Feature_Class
{

	//----------------------------------------------------------------------------------------- $keep
	/**
	 * @store false
	 * @var boolean
	 */
	public bool $keep = false;

}
