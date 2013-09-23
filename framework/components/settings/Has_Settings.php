<?php
namespace SAF\Framework;

/**
 * For any class that can apply specific settings
 *
 * ie User, but other classes of an enterprise resource planning softway may use it :
 * Company, User_Group, etc.
 */
trait Has_Settings
{

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @link Collection
	 * @var Setting
	 */
	public $settings;

}
