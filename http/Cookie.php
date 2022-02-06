<?php
namespace ITRocks\Framework\Http;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Tools\Stringable;
use ITRocks\Framework\Traits\Has_Name;

/**
 * HTTP header cookie object
 */
class Cookie implements Stringable
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[]
	 */
	public array $properties;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public string $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name       string|null
	 * @param $value      string|null
	 * @param $properties string[]|null
	 */
	public function __construct(string $name = null, string $value = null, array $properties = null)
	{
		if (isset($name))              $this->name       = $name;
		if (isset($value))             $this->value      = $value;
		if (isset($properties))        $this->properties = $properties;
		if (!isset($this->properties)) $this->properties = [];
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$string = $this->name . '=' . $this->value;
		foreach ($this->properties as $key => $value) {
			$string .= '; ' . $key . '=' . $value;
		}
		return $string;
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $string string
	 * @return static
	 */
	public static function fromString(string $string) : static
	{
		/** @noinspection PhpUnhandledExceptionInspection static */
		$cookie = Builder::create(static::class);
		$cookie->properties = [];
		foreach (explode(';', $string) as $element) {
			list($key, $value) = explode('=', $element);
			if (!isset($cookie->name)) {
				$cookie->name  = trim($key);
				$cookie->value = trim($value);
			}
			else {
				$cookie->properties[trim($key)] = trim($value);
			}
		}
		return $cookie;
	}

}
