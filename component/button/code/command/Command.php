<?php
namespace ITRocks\Framework\Component\Button\Code;

/**
 * A command to execute
 */
interface Command
{

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $object object
	 * @return mixed
	 */
	public function execute(object $object) : mixed;

}
