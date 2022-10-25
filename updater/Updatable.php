<?php
namespace ITRocks\Framework\Updater;

/**
 * An interface for all updatable classes (in most of cases they should be plugins too)
 */
interface Updatable
{

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer
	 */
	public function update(int $last_time);

}
