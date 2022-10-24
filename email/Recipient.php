<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Component\Combo\Fast_Add;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Traits\Has_Email;
use ITRocks\Framework\Traits\Has_Name;

/**
 * An email recipient (or sender, this object can be used for both)
 *
 * @business
 * @override name @mandatory false
 * @representative name, email
 * @sort name, email
 */
class Recipient implements Fast_Add
{
	use Has_Email;
	use Has_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $email string|null
	 * @param $name  string|null
	 */
	public function __construct(string $email = null, string $name = null)
	{
		if (isset($email)) $this->email = $email;
		if (isset($name))  $this->name  = $name;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return sprintf(
			$this->name ? '%s <%s>' : '%s%s',
			str_replace([DQ, '<', '>'], [BS . DQ, '', ''], $this->name ?? ''),
			$this->email
		);
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return ?static
	 */
	public static function fromString(string $string) : ?static
	{
		if (!trim($string)) {
			return null;
		}
		$string    = cleanSpaces($string);
		$recipient = Search_Object::create(static::class);
		if (str_contains($string, '<') && str_contains($string, '>')) {
			$recipient->name  = noQuotes(trim(lParse($string, '<')));
			$recipient->email = trim(mParse($string, '<', '>'));
		}
		else {
			$recipient->name  = '';
			$recipient->email = $string;
		}
		return Dao::searchOne($recipient) ?: $recipient;
	}

}
