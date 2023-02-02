<?php
namespace ITRocks\Framework\Feature\Import;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Feature\Import\Settings\Import_Preview;
use ITRocks\Framework\Feature\Import\Settings\Import_Settings;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
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
	public File $file;

	//-------------------------------------------------------------------------------------- $preview
	#[Getter('getPreview')]
	public Import_Preview $preview;

	//------------------------------------------------------------------------------------- $settings
	public Import_Settings $settings;

	//----------------------------------------------------------------------------------- __construct
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
	 * @noinspection PhpUnused #Getter
	 */
	protected function getPreview() : Import_Preview
	{
		if (!isset($this->preview)) {
			$this->preview = new Import_Preview($this->file->getCsvContent());
		}
		return $this->preview;
	}

}
