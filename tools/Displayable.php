<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Reflection\Attribute\Property\Values;

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
	const TYPE_CLASS = 'class';

	//----------------------------------------------------------------------------------- TYPE_METHOD
	const TYPE_METHOD = 'method';

	//--------------------------------------------------------------------------------- TYPE_PROPERTY
	const TYPE_PROPERTY = 'property';

	//----------------------------------------------------------------------------------- TYPE_STRING
	const TYPE_STRING = 'string';

	//----------------------------------------------------------------------------------------- $type
	#[Values('class, method, property, string')]
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
	/** Return value encoded with json */
	public function json() : string
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return is_object($this->value)
			? (new Json)->encodeObject($this->value)
			: jsonEncode($this->value);
	}

	//----------------------------------------------------------------------------------------- lower
	public function lower() : static
	{
		return new static(strtolower($this->display()));
	}

	//--------------------------------------------------------------------------------------- ucfirst
	public function ucfirst() : static
	{
		return new static(ucfirst($this->display()));
	}

	//--------------------------------------------------------------------------------------- ucwords
	public function ucwords() : static
	{
		return new static(ucwords($this->display()));
	}

	//----------------------------------------------------------------------------------------- under
	public function under() : static
	{
		return new static(str_replace(SP, '_', $this->display()));
	}

	//----------------------------------------------------------------------------------------- upper
	public function upper() : static
	{
		return new static(strtoupper($this->display()));
	}

	//------------------------------------------------------------------------------------------- uri
	public function uri() : static
	{
		return new static(strUri($this->value));
	}

	//------------------------------------------------------------------------------------ uriElement
	public function uriElement() : static
	{
		return new static(strUriElement($this->value));
	}

}
