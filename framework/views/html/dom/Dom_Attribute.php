<?php
namespace SAF\Framework;

/**
 * A DOM attribute class
 */
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
	 * @param $name string
	 * @param $value string
	 */
	public function __construct($name = null, $value = null)
	{
		if (isset($name))  $this->name = $name;
		if (isset($value)) $this->value = $value;
	}

	//----------------------------------------------------------------------------------- escapeValue
	/**
	 * @param $value string
	 * @return string
	 */
	public static function escapeValue($value)
	{
		if (strpos($value, DQ) === false) {
			return DQ . $value . DQ;
		}
		elseif (strpos($value, Q) === false) {
			return Q . $value . Q;
		}
		else {
			return DQ . htmlspecialchars($value) . DQ;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		if ($this->value === true) {
			return $this->name;
		}
		elseif ($this->value === false) {
			return '';
		}
		else {
			return $this->name . (isset($this->value) ? ('=' . self::escapeValue($this->value)) : '');
		}
	}

}
