<?php
namespace ITRocks\Framework\Feature\Import;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Feature\Import\Settings\Import_Preview;
use ITRocks\Framework\Feature\Import\Settings\Import_Settings;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Import worksheet
 */
class Import_Worksheet
{
	use Has_Name;

	//--------------------------------------------------------------------------------------- $errors
	/**
	 * Csv import errors list (ie unsolved references)
	 *
	 * @var string[]
	 */
	public $errors;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var File
	 */
	public $file;

	//-------------------------------------------------------------------------------------- $preview
	/**
	 * @getter getPreview
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
	 * @param $file     File
	 * @param $preview  Import_Preview
	 */
	public function __construct(
		$name = null, Import_Settings $settings = null, $file = null, Import_Preview $preview = null
	) {
		if (isset($file))     $this->file     = $file;
		if (isset($name))     $this->name     = $name;
		if (isset($preview))  $this->preview  = $preview;
		if (isset($settings)) $this->settings = $settings;
	}

	//------------------------------------------------------------------------------------ getPreview
	/**
	 * @return Import_Preview
	 */
	protected function getPreview()
	{
		if (!isset($this->preview)) {
			$this->preview = new Import_Preview($this->file->getCsvContent());
		}
		return $this->preview;
	}

}
