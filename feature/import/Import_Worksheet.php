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
	public array $errors;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var File
	 */
	public File $file;

	//-------------------------------------------------------------------------------------- $preview
	/**
	 * @getter
	 * @var Import_Preview
	 */
	public Import_Preview $preview;

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @var Import_Settings
	 */
	public Import_Settings $settings;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name     string|null
	 * @param $settings Import_Settings|null
	 * @param $file     File|null
	 * @param $preview  Import_Preview|null
	 */
	public function __construct(
		string $name = null, Import_Settings $settings = null, File $file = null,
		Import_Preview $preview = null
	) {
		if (isset($file))     $this->file     = $file;
		if (isset($name))     $this->name     = $name;
		if (isset($preview))  $this->preview  = $preview;
		if (isset($settings)) $this->settings = $settings;
	}

	//------------------------------------------------------------------------------------ getPreview
	/**
	 * @noinspection PhpUnused @getter
	 * @return Import_Preview
	 */
	protected function getPreview() : Import_Preview
	{
		if (!isset($this->preview)) {
			$this->preview = new Import_Preview($this->file->getCsvContent());
		}
		return $this->preview;
	}

}
