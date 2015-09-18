<?php
namespace SAF\Framework\Widget\Button;

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $source string
	 */
	public function __construct($source = null, $when = null, $feature = null)
	{
		if (isset($source)) {
			$this->source = $source;
		}
		if (isset($when)) {
			$this->when = $when;
		}
		if (isset($feature)) {
			$this->feature = $feature;
		}
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $object    object
	 * @param $condition boolean
	 * @return boolean true if all the commands returned a non-empty value
	 */
	public function execute($object, $condition = false)
	{
		$result = true;
		foreach (explode(LF, $this->source) as $command) {
			if ($command = Parser::parse($command, $condition)) {
				// execute() before $result, because each command must be executed
				$more = $command->execute($object);
				$result = $more && $result;
			}
		}
		return $result;
	}

}
