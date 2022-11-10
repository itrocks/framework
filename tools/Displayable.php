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
	private string $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $type  string the type of the displayable object : class, method, property or string
	 */
	public function __construct(string $value, string $type = self::TYPE_STRING)
	{
		parent::__construct($value);
		$this->type = $type;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display() : string
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function json() : string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return is_object($this->value)
			? (new Json)->encodeObject($this->value)
			: jsonEncode($this->value);
	}

	//----------------------------------------------------------------------------------------- lower
	/**
	 * @return static
	 */
	public function lower() : static
	{
		return new static(strtolower($this->display()));
	}

	//--------------------------------------------------------------------------------------- ucfirst
	/**
	 * @return static
	 */
	public function ucfirst() : static
	{
		return new static(ucfirst($this->display()));
	}

	//--------------------------------------------------------------------------------------- ucwords
	/**
	 * @return static
	 */
	public function ucwords() : static
	{
		return new static(ucwords($this->display()));
	}

	//----------------------------------------------------------------------------------------- under
	/**
	 * @return static
	 */
	public function under() : static
	{
		return new static(str_replace(SP, '_', $this->display()));
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return static
	 */
	public function upper() : static
	{
		return new static(strtoupper($this->display()));
	}

	//------------------------------------------------------------------------------------------- uri
	/**
	 * @return static
	 */
	public function uri() : static
	{
		return new static(strUri($this->value));
	}

	//------------------------------------------------------------------------------------ uriElement
	/**
	 * @return static
	 */
	public function uriElement() : static
	{
		return new static(strUriElement($this->value));
	}

}
