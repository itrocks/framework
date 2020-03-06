<?php
namespace ITRocks\Framework\Tools;

/**
 * Display methods for a displayable object
 *
 * Mainly built from any reflection class, method, or property names.
 *
 * @override value @var object|string|null
 * @property object|string|null value anything that can be displayed using strval()
 */
class Displayable extends String_Class
{

	//------------------------------------------------------------------------------------ TYPE_CLASS
	/**
	 * @var string 'class'
	 */
	const TYPE_CLASS = 'class';

	//----------------------------------------------------------------------------------- TYPE_METHOD
	/**
	 * @var string 'method'
	 */
	const TYPE_METHOD = 'method';

	//--------------------------------------------------------------------------------- TYPE_PROPERTY
	/**
	 * @var string 'property'
	 */
	const TYPE_PROPERTY = 'property';

	//----------------------------------------------------------------------------------- TYPE_STRING
	/**
	 * @var string 'string'
	 */
	const TYPE_STRING = 'string';

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values class, method, property, string
	 * @var string
	 */
	private $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $type  string the type of the displayable object : class, method, property or string
	 */
	public function __construct($value, $type)
	{
		parent::__construct($value);
		$this->type = $type;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		switch ($this->type) {
			case self::TYPE_CLASS:    return Names::classToDisplay($this->value);
			case self::TYPE_METHOD:   return Names::methodToDisplay($this->value);
			case self::TYPE_PROPERTY: return Names::propertyToDisplay($this->value);
		}
		return strval($this->value);
	}

	//------------------------------------------------------------------------------------------ json
	/**
	 * Return value encoded with json
	 *
	 * @return string
	 */
	public function json()
	{
		return is_object($this->value)
			? (new Json)->encodeObject($this->value)
			: json_encode($this->value);
	}

	//----------------------------------------------------------------------------------------- lower
	/**
	 * @return string
	 */
	public function lower()
	{
		return strtolower($this->display());
	}

	//--------------------------------------------------------------------------------------- ucfirst
	/**
	 * @return string
	 */
	public function ucfirst()
	{
		return ucfirst($this->display());
	}

	//--------------------------------------------------------------------------------------- ucwords
	/**
	 * @return string
	 */
	public function ucwords()
	{
		return ucwords($this->display());
	}

	//----------------------------------------------------------------------------------------- under
	/**
	 * @return string
	 */
	public function under()
	{
		return str_replace(SP, '_', $this->display());
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return string
	 */
	public function upper()
	{
		return strtoupper($this->display());
	}

	//------------------------------------------------------------------------------------------- uri
	/**
	 * @return string
	 */
	public function uri()
	{
		return strUri($this->value);
	}

	//------------------------------------------------------------------------------------ uriElement
	/**
	 * @return string
	 */
	public function uriElement()
	{
		return strUriElement($this->value);
	}

}
