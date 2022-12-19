<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao\File\Session_File;
use ITRocks\Framework\Dao\File\Spreadsheet_File;
use ITRocks\Framework\Dao\File\Type;
use ITRocks\Framework\Dao\File\Type_Builder;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\View;

/**
 * File is a simple business object that stores files
 *
 * @before_write getContent
 */
class File
{
	use Has_Name;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @binary
	 * @getter
	 * @impacts hash, updated_on
	 * @max_length 4000000000
	 * @setter
	 * @var ?string Null if the file does not exist (no content)
	 */
	public ?string $content = null;

	//----------------------------------------------------------------------------------------- $hash
	/**
	 * @getter
	 * @var string
	 */
	public string $hash = '';

	//-------------------------------------------------------------------------- $temporary_file_name
	/**
	 * Temporary file name where the file is stored, used to get content into $content only if needed
	 *
	 * @getter
	 * @setter
	 * @var string
	 */
	public string $temporary_file_name = '';

	//----------------------------------------------------------------------------------- $updated_on
	/**
	 * @link DateTime
	 * @mandatory
	 * @var Date_Time|string
	 */
	public Date_Time|string $updated_on;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $temporary_file_name string|null
	 */
	public function __construct(string $temporary_file_name = null)
	{
		if (isset($temporary_file_name)) {
			if (empty($this->name)) {
				$this->name = rLastParse($temporary_file_name, SL, 1, true);
			}
			$this->temporary_file_name = $temporary_file_name;
		}
		if (!isset($this->updated_on)) {
			/** @noinspection PhpUnhandledExceptionInspection constant */
			$this->updated_on = Builder::create(Date_Time::class);
		}
	}

	//-------------------------------------------------------------------------------------- calcHash
	/**
	 * Calculate hash code
	 */
	protected function calcHash() : void
	{
		$this->hash = isset($this->content) ? hash('512', $this->content) : '';
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * Gets $this->content, or load it from temporary file name if not set
	 *
	 * @return ?string
	 */
	public function getContent() : ?string
	{
		if ($this->temporary_file_name && !isset($this->content)) {
			$this->content = file_exists($this->temporary_file_name)
				? file_get_contents($this->temporary_file_name)
				: null;
		}
		return $this->content;
	}

	//--------------------------------------------------------------------------------- getCsvContent
	/**
	 * @param $errors string[]
	 * @return array Two-dimensional array (keys are row, column)
	 */
	public function getCsvContent(array &$errors = []) : array
	{
		return (new Spreadsheet_File)->readCsvFile($this->temporary_file_name, $errors);
	}

	//--------------------------------------------------------------------------------------- getHash
	/**
	 * Gets $hash, or calculate it from content if not set
	 *
	 * @noinspection PhpUnused @getter
	 * @return string
	 */
	protected function getHash() : string
	{
		if (empty($this->hash)) {
			$this->calcHash();
		}
		return $this->hash;
	}

	//-------------------------------------------------------------------------- getTemporaryFileName
	/**
	 * Gets temporary file name, or write content into a temporary file name and get this name if not
	 * set or file does not exist
	 *
	 * @noinspection PhpUnused @getter
	 * @return string
	 */
	protected function getTemporaryFileName() : string
	{
		if (
			isset($this->content)
			&& (empty($this->temporary_file_name) || !file_exists($this->temporary_file_name))
		) {
			$this->temporary_file_name = Application::current()->getTemporaryFilesPath() . SL
				. uniqid() . '_' . $this->name;
			if (str_contains($this->temporary_file_name, SL)) {
				Files::mkdir(lLastParse($this->temporary_file_name, SL));
			}
			file_put_contents($this->temporary_file_name, $this->content);
		}
		return $this->temporary_file_name;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return Type
	 */
	public function getType() : Type
	{
		return Type_Builder::build($this->name);
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * Build a link to the file using the default view engine
	 *
	 * The file will be stored into a server-side local temporary storage, to be available by the link
	 *
	 * @param $feature    string
	 * @param $parameters mixed additional link parameters
	 * @return string
	 */
	public function link(string $feature = Feature::F_OUTPUT, mixed $parameters = []) : string
	{
		$hash = $this->nameHash();
		/** @var $session_files Session_File\Files */
		$session_files               = Session::current()->get(Session_File\Files::class, true);
		$session_files->files[$hash] = $this;

		if (isset($parameters) && !is_array($parameters)) {
			$parameters = [$parameters];
		}
		$parameters = array_merge([$hash], $parameters);

		return View::link(Session_File::class, $feature, $parameters);
	}

	//-------------------------------------------------------------------------------------- nameHash
	/**
	 * @return string
	 */
	public function nameHash() : string
	{
		return hash('sha512', $this->name ?: $this->temporary_file_name ?: '');
	}

	//----------------------------------------------------------------------------------- previewLink
	/**
	 * Generate a file preview link
	 * - image : the link to the image, with the given size
	 * - another file type : the link to the file type icon
	 *
	 * @noinspection PhpUnused @getter
	 * @param $size integer
	 * @return string
	 */
	public function previewLink(int $size = 22) : string
	{
		$image_file = $this;
		if (!$this->getType()->is('image')) {
			$extension = rLastParse($this->name, DOT);
			$path      = __DIR__ . '/../../skins/default/img/ext/';
			for ($try = 0; $try < 2; $try ++) {
				foreach (['gif', 'png', 'svg'] as $image_extension) {
					if (file_exists($path . $extension . DOT . $image_extension)) {
						$image_file = new File($path . $extension . DOT . $image_extension);
						break 2;
					}
				}
				$extension = 'doc';
			}
		}
		return $image_file->link(Feature::F_OUTPUT, $size);
	}

	//------------------------------------------------------------------------------------ setContent
	/**
	 * @param $content ?string
	 */
	protected function setContent(?string $content) : void
	{
		$old_hash      = $this->hash;
		$this->content = $content;
		$this->calcHash();
		if ($this->hash !== $old_hash) {
			$this->temporary_file_name = '';
		}
		$this->updated_on = new Date_Time();
	}

	//-------------------------------------------------------------------------- setTemporaryFileName
	/**
	 * @noinspection PhpUnused @getter
	 * @param $temporary_file_name string
	 */
	protected function setTemporaryFileName(string $temporary_file_name) : void
	{
		if ($temporary_file_name && file_exists($temporary_file_name)) {
			$this->content = null;
		}
		$this->temporary_file_name = $temporary_file_name;
	}

	//------------------------------------------------------------------------------------------ size
	/**
	 * @return integer
	 */
	public function size() : int
	{
		return filesize($this->temporary_file_name);
	}

}
