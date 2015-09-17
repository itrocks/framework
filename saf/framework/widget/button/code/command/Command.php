<?php
namespace SAF\Framework\Widget\Button\Code;

use SAF\Framework\Controller\Uri;

/**
 * A command to execute
 */
interface Command
{

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $uri Uri
	 */
	public function execute(Uri $uri);

}
