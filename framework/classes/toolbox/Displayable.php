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
	 * @values "class", "method", "property", "string"
	 */
	private $type;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($value, $type)
	{
		parent::__construct($value);
		$this->type = $type;
	}

	//--------------------------------------------------------------------------------------- display
	public function display()
	{
		switch ($this->type) {
			case Displayable::TYPE_CLASS:    return Names::classToDisplay($this->value);
			case Displayable::TYPE_METHOD:   return Names::methodToDisplay($this->value);
			case Displayable::TYPE_PROPERTY: return Names::propertyToDisplay($this->value);
		}
		return $this->value;
	}

	//----------------------------------------------------------------------------------------- lower
	public function lower()
	{
		return strtolower($this->display());
	}

	//--------------------------------------------------------------------------------------- ucfirst
	public function ucfirst()
	{
		return ucfirst($this->display());
	}

	//--------------------------------------------------------------------------------------- ucwords
	public function ucwords()
	{
		return ucwords($this->display());
	}

	//----------------------------------------------------------------------------------------- under
	public function under()
	{
		return str_replace(" ", "_", $this->display());
	}

	//----------------------------------------------------------------------------------------- upper
	public function upper()
	{
		return strtoupper($this->display());
	}

}
