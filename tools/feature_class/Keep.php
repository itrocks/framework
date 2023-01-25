<?php
namespace ITRocks\Framework\Tools\Feature_Class;

use AllowDynamicProperties;
use ITRocks\Framework\Tools\Feature_Class;

/**
 * Feature class with the 'done' information
 *
 * @business false
 * @store_name feature_classes
 * @todo Remove AllowDynamicProperties where $id will be general to all @stored classes
 */
#[AllowDynamicProperties]
class Keep extends Feature_Class
{

	//----------------------------------------------------------------------------------------- $keep
	/**
	 * @store false
	 * @var boolean
	 */
	public bool $keep = false;

}
