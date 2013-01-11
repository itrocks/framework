<?php
namespace SAF\Framework;

class Displayable extends String
{

	//---------------------------------------------------------------------------------- const TYPE_*
	const TYPE_CLASS    = "class";
	const TYPE_METHOD   = "method";
	const TYPE_PROPERTY = "property";
	const TYPE_STRING   = "string";

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 * @values class, method, property, string
	 */
	private $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 * @param string $type the type of the displayable object : class, method, property or string
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
		return $this->value;
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
		return str_replace(" ", "_", $this->display());
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return string
	 */
	public function upper()
	{
		return strtoupper($this->display());
	}

}
