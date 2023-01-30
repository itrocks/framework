<?php
namespace ITRocks\Framework\Tools\Feature_Class;

use AllowDynamicProperties;
use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Tools\Feature_Class;

/**
 * Feature class with the 'done' information
 *
 * @business false
 * @todo Remove AllowDynamicProperties where $id will be general to all @stored classes
 */
#[AllowDynamicProperties]
#[Store_Name('feature_classes')]
class Keep extends Feature_Class
{

	//----------------------------------------------------------------------------------------- $keep
	/**
	 * @store false
	 * @var boolean
	 */
	public bool $keep = false;

}
