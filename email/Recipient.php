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
	 * @param $email string
	 * @param $name  string
	 */
	public function __construct($email = null, $name = null)
	{
		if (isset($email)) $this->email = $email;
		if (isset($name))  $this->name  = $name;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			$this->name ? '%s <%s>' : '%s%s',
			str_replace([DQ, '<', '>'], [BS . DQ, '', ''], $this->name),
			$this->email
		);
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $string string
	 * @return static
	 */
	public static function fromString($string)
	{
		if (!trim($string)) {
			return null;
		}
		$string    = cleanSpaces($string);
		$recipient = Search_Object::create(static::class);
		if ((strpos($string, '<') !== false) && strpos($string, '>')) {
			$recipient->name  = noQuotes(trim(lParse($string, '<')));
			$recipient->email = trim(mParse($string, '<', '>'));
		}
		else {
			$recipient->name  = '';
			$recipient->email = $string;
		}
		$recipient = Dao::searchOne($recipient) ?: $recipient;
		return $recipient;
	}

	//---------------------------------------------------------------------------------------- toMIME
	/**
	 * @return string
	 */
	public function toMIME()
	{
		return sprintf(
			$this->name ? '"%s" <%s>' : '%s%s',
			str_replace([DQ, '<', '>'], [BS . DQ, '', ''], $this->name),
			$this->email
		);
	}

}
