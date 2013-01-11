<?php
namespace SAF\Framework;

class Dom_Attribute
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name = null, $value = null)
	{
		if (isset($name))  $this->name = $name;
		if (isset($value)) $this->value = $value;
	}

	//----------------------------------------------------------------------------------- escapeValue
	/**
	 * @param string $value
	 * @return string
	 */
	public static function escapeValue($value)
	{
		if (strpos($value, '"') === false) {
			return '"' . $value . '"';
		}
		elseif (strpos($value, "'") === false) {
			return "'" . $value . "'";
		}
		else {
			return '"' . htmlspecialchars($value) . '"';
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		return $this->name . "=" . self::escapeValue($this->value);
	}

}
