<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM attribute class
 */
class Attribute
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public string $name;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var bool|int|string|null
	 */
	public bool|int|string|null $value = null;

	//---------------------------------------------------------------------------- BOOLEAN_ATTRIBUTES
	/**
	 * These attributes name accept only boolean values, but they must parse in HTML like this :
	 * true => 'attributeName' ; false => '' (not parsed
	 *
	 * Other attributes will be parsed the standard way :
	 * true => 'attributeName="1"' ; false => 'attributeName=""'
	 *
	 * @example
	 * - value can accept boolean and must be value="1" for true, or value="" for false
	 * - readonly will be true or false, and will be displayed as 'readonly' for true or '' for false
	 * @see https://html.spec.whatwg.org/#attributes-3
	 */
	const BOOLEAN_ATTRIBUTES = [
		'allowfullscreen', 'allowpaymentrequest', 'allowusermedia', 'async', 'autofocus', 'autoplay',
		'checked', 'controls', 'data-sensitive', 'default', 'defer', 'disabled', 'formnovalidate',
		'hidden', 'ismap', 'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open',
		'playsinline', 'readonly', 'required', 'reversed', 'selected', 'truespeed', 'typemustmatch'
	];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string|null
	 * @param $value bool|int|string|null
	 */
	public function __construct(string $name = null, bool|int|string $value = null)
	{
		if (isset($name))  $this->name  = $name;
		if (isset($value)) $this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		// boolean attributes are returned as 'here' / 'not here'
		if (is_bool($this->value) && in_array(strtolower($this->name), static::BOOLEAN_ATTRIBUTES)) {
			return $this->value ? $this->name : '';
		}
		// non-boolean attributes are returned with their value, don't care what it is
		// (true => "1", false => "")
		return $this->name . (isset($this->value) ? ('=' . self::escapeValue($this->value)) : '');
	}

	//----------------------------------------------------------------------------------- escapeValue
	/**
	 * @param $value bool|int|string
	 * @return string
	 */
	public static function escapeValue(bool|int|string|null $value) : string
	{
		if (!str_contains($value, DQ)) {
			return DQ . $value . DQ;
		}
		elseif (!str_contains($value, Q)) {
			return Q . $value . Q;
		}
		return DQ . htmlspecialchars($value) . DQ;
	}

}
