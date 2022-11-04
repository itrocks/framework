<?php
namespace ITRocks\Framework\Traits;

/**
 * For objects than can be deleted without being purged from the database
 */
trait Has_Deleted
{

	//-------------------------------------------------------------------------------------- $deleted
	/**
	 * @var boolean
	 */
	public bool $deleted = false;

}
