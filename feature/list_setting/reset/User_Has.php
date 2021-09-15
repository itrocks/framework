<?php
namespace ITRocks\Framework\Feature\List_Setting\Reset;

use ITRocks\Framework\Feature\List_Setting\Reset;
use ITRocks\Framework\User;

/**
 * User has a reset list setting property
 *
 * @extends User
 * @see User
 */
trait User_Has
{

	//---------------------------------------------------------------------------------- $reset_lists
	/**
	 * @see Reset
	 * @values Reset::const
	 * @var string
	 */
	public $reset_lists = '';

}
