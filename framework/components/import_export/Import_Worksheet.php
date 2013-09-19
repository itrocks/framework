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
		$name = null, Import_Settings $settings = null, $file = null, Import_Preview $preview = null
	) {
		if (isset($name))     $this->name     = $name;
		if (isset($settings)) $this->settings = $settings;
		if (isset($preview))  $this->preview  = $preview;
		if (isset($file)) {
			$this->file = $file;
			if (!isset($this->preview)) {
				$this->preview = new Import_Preview($this->getCsvContent());
			}
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

	//--------------------------------------------------------------------------------- getCsvContent
	/**
	 * @return array Two dimensional array (keys are row, column)
	 */
	public function getCsvContent()
	{
		return array_map("str_getcsv", file($this->file->temporary_file_name));
	}

}
