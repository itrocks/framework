<?php
namespace ITRocks\Framework\Tools\Feature_Class;

use AllowDynamicProperties;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Tools\Feature_Class;

/**
 * Feature class with the 'done' information
 *
 * @todo Remove #AllowDynamicProperties where $id will be general to all #Store classes
 */
#[Class_\Store(false)]
#[AllowDynamicProperties]
class Keep extends Feature_Class
{

	//----------------------------------------------------------------------------------------- $keep
	#[Store(false)]
	public bool $keep = false;

}
