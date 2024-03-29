<?php
namespace ITRocks\Framework\Component\Button;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Button\Code\Command\Parser;
use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Tools\Stringable;

/**
 * Dynamic source code typed in by the user
 */
#[Store('button_codes')]
class Code implements Stringable
{

	//-------------------------------------------------------------------------------------- $feature
	#[User(User::INVISIBLE)]
	public string $feature;

	//--------------------------------------------------------------------------------------- $source
	#[Alias('source_code'), Max_Length(60000), Multiline]
	public string $source;

	//----------------------------------------------------------------------------------------- $when
	#[User(User::INVISIBLE), Values('after', 'before')]
	public string $when;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $source  string|null
	 * @param $when    string|null @values after, before
	 * @param $feature string|null
	 */
	public function __construct(string $source = null, string $when = null, string $feature = null)
	{
		if (isset($feature)) $this->feature = $feature;
		if (isset($source))  $this->source  = $source;
		if (isset($when))    $this->when    = $when;
	}

	//------------------------------------------------------------------------------------ __toString
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
		foreach (explode(LF, $this->source ?? '') as $command) {
			if ($command = Parser::parse($command, $condition)) {
				// execute() before $result, because each command must be executed
				$more   = $command->execute($object);
				$result = $more && $result;
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------------ fromString
	public static function fromString(string $string) : ?static
	{
		/** @noinspection PhpUnhandledExceptionInspection static */
		return Builder::create(static::class, [$string]);
	}

}
