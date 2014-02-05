<?php
namespace SAF\Framework;

/**
 * File is a simple business object that stores files
 */
class File
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @binary
	 * @getter getContent
	 * @max_length 4000000000
	 * @setter setContent
	 * @var string
	 */
	public $content;

	//----------------------------------------------------------------------------------------- $hash
	/**
	 * @getter getHash
	 * @var string
	 */
	public $hash;

	//-------------------------------------------------------------------------- $temporary_file_name
	/**
	 * Temporary file name where the file is stored, used to get content into $content only if needed
	 *
	 * @getter getTemporaryFileName
	 * @var string
	 */
	public $temporary_file_name;

	//-------------------------------------------------------------------------- $temporary_file_name
	/**
	 * @param $temporary_file_name string
	 */
	public function __construct($temporary_file_name = null)
	{
		if (isset($temporary_file_name)) {
			if (!isset($this->name)) {
				$this->name = rLastParse($temporary_file_name, "/", 1, true);
			}
			$this->temporary_file_name = $temporary_file_name;
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

	//-------------------------------------------------------------------------------------- calcHash
	/**
	 * Calculate hash code
	 */
	protected function calcHash()
	{
		$this->hash = md5($this->content);
	}

	//--------------------------------------------------------------------------------- getCsvContent
	/**
	 * @param $errors string[]
	 * @return array Two dimensional array (keys are row, column)
	 */
	public function getCsvContent(&$errors = array())
	{
		return Spreadsheet_File::readCsvFile($this->temporary_file_name, $errors);
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * Gets $content, or load it from temporary file name if not set
	 *
	 * @param string
	 */
	public function getContent(&$content)
	{
		if (isset($this->temporary_file_name) && !isset($content)) {
			$content = file_get_contents($this->temporary_file_name);
			$this->calcHash();
		}
	}

	//--------------------------------------------------------------------------------------- getHash
	/**
	 * Gets $hash, or calculate it from content if not set
	 *
	 * @param string
	 */
	public function getHash(&$hash)
	{
		if (!isset($hash)) {
			$this->calcHash();
		}
	}

	//---------------------------------------------------------------------------getTemporaryFileName
	/**
	 * Gets temporary file name, or write content into a temporary file name and get this name if not
	 * set or file does not exist
	 *
	 * @param $temporary_file_name string
	 */
	public function getTemporaryFileName(&$temporary_file_name)
	{
		if (
			isset($this->content)
			&& (empty($temporary_file_name) || !file_exists($temporary_file_name))
		) {
			if (empty($temporary_file_name)) {
				$temporary_file_name = Application::current()->getTemporaryFilesPath() . "/"
					. uniqid() . "_" . $this->name;
			}
			if (strpos($temporary_file_name, "/") !== false) {
				Files::mkdir(lLastParse($temporary_file_name, "/"));
			}
			file_put_contents($temporary_file_name, $this->content);
		}
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return File_Type
	 */
	public function getType()
	{
		return File_Type_Builder::build($this->name);
	}

	//------------------------------------------------------------------------------------ setContent
	public function setContent()
	{
		$this->calcHash();
	}

}
