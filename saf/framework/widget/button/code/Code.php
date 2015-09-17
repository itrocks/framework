<?php
namespace SAF\Framework\Widget\Button;

use SAF\Framework\Controller\Uri;
use SAF\Framework\Widget\Button\Code\Command;
use SAF\Framework\Widget\Button\Code\Command\Parser;

/**
 * Dynamic source code typed in by the user
 *
 * @business
 */
class Code
{

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @user invisible
	 * @var string
	 */
	public $feature;

	//--------------------------------------------------------------------------------------- $source
	/**
	 * @max_length 60000
	 * @multiline
	 * @var string
	 */
	public $source;

	//----------------------------------------------------------------------------------------- $when
	/**
	 * @user invisible
	 * @values after, before
	 * @var string
	 */
	public $when;

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $uri Uri
	 */
	public function execute(Uri $uri)
	{
		foreach (explode(LF, $this->source) as $command) {
			if ($command = Parser::parse($command)) {
				$command->execute($uri);
			}
		}
	}

}
