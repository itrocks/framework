<?php
namespace ITRocks\Framework\Widget\Button\Code;

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
	public function execute($object);

}
