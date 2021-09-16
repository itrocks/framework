<?php
namespace ITRocks\Framework\Report\Dashboard\Indicator\Property_Path;

/**
 * A property path value
 */
class Value
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * @var string
	 */
	public string $display;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public string $path;

	//------------------------------------------------------------------------------------- $selected
	/**
	 * @var boolean
	 */
	public bool $selected;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $path     string
	 * @param $display  string
	 * @param $selected boolean
	 */
	public function __construct(string $path, string $display, bool $selected)
	{
		$this->display  = $display;
		$this->path     = $path;
		$this->selected = $selected;
	}

}
