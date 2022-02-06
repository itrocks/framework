<?php
namespace ITRocks\Framework\Component\Button;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Button\Code\Command\Parser;
use ITRocks\Framework\Tools\Stringable;

/**
 * Dynamic source code typed in by the user
 *
 * @business
 */
class Code implements Stringable
{

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @user invisible
	 * @var string
	 */
	public string $feature;

	//--------------------------------------------------------------------------------------- $source
	/**
	 * @alias source_code
	 * @max_length 60000
	 * @multiline
	 * @var string
	 */
	public string $source;

	//----------------------------------------------------------------------------------------- $when
	/**
	 * @user invisible
	 * @values after, before
	 * @var string
	 */
	public string $when;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $source  string|null
	 * @param $when    string|null @values after, before
	 * @param $feature string|null
	 */
	public function __construct(string $source = null, string $when = null, string $feature = null)
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->source;
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $object    object
	 * @param $condition boolean
	 * @return boolean true if all the commands returned a non-empty value
	 */
	public function execute(object $object, bool $condition = false) : bool
	{
		$result = true;
		foreach (explode(LF, $this->source) as $command) {
			if ($command = Parser::parse($command, $condition)) {
				// execute() before $result, because each command must be executed
				$more   = $command->execute($object);
				$result = $more && $result;
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $source string
	 * @return static
	 */
	public static function fromString(string $source) : static
	{
		/** @noinspection PhpUnhandledExceptionInspection static */
		return Builder::create(static::class, [$source]);
	}

}
