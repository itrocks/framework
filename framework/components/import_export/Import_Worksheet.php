<?php
namespace SAF\Framework;

/**
 * Import worksheet
 */
class Import_Worksheet
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var string
	 */
	public $file;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $preview
	/**
	 * @var Import_Preview
	 */
	public $preview;

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @var Import_Settings
	 */
	public $settings;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name     string
	 * @param $settings Import_Settings
	 * @param $preview  Import_Preview
	 * @param $file     File
	 */
	public function __construct(
		$name = null, Import_Settings $settings = null, Import_Preview $preview = null, $file = null
	) {
		if (isset($name))     $this->name     = $name;
		if (isset($preview))  $this->preview  = $preview;
		if (isset($settings)) $this->settings = $settings;
		if (isset($file))     $this->file     = $file;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
