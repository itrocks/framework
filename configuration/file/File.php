<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Configuration\File\Has_File_Name;
use ITRocks\Framework\Configuration\File\Read;
use ITRocks\Framework\Configuration\File\Write;

/**
 * Configuration file
 */
class File
{
	use Has_File_Name;

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * Configuration file content
	 *
	 * @var string[]
	 */
	protected $configuration;

	//------------------------------------------------------------------------------- $included_files
	/**
	 * Included configuration sub-files, if there are some (eg builder.php, menu.php, etc.)
	 *
	 * @var File[]
	 */
	protected $included_files = [];

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds sub-elements to a sub-section path
	 *
	 * @param $path string[]
	 * @param $add  array
	 */
	public function add(array $path, array $add)
	{
		array_unshift($path, '] = [');
		echo '+ add into ' . print_r($path, true) . BRLF;
		$configuration = reset($this->configuration);
		$positions     = [];
		foreach ($path as $path_element) {
			$depth = 0;
			while (
				($configuration !== false)
				&& (
					!$depth
					|| !strpos($configuration, $path_element)
					|| !preg_match("/^\s*" . preg_quote($path_element) . "\s*=>/", $configuration)
				)
			) {
				if (substr($configuration, -1) === '[') {
					$depth ++;
				}
				elseif ((substr($configuration, -1) === ']') || (substr($configuration, -2) === '],')) {
					$depth --;
				}
				$configuration = next($this->configuration);
			}
			if ($configuration === false) {
				trigger_error('not found ' . $path_element . ' into ' . $this->file_name, E_USER_ERROR);
			}
			else {
				echo '- found ' . $path_element . ' at position ' . key($this->configuration) . BRLF;
				$positions[] = key($this->configuration);
			}
		}
		if ($configuration !== false) {
			echo '- insert ' . print_r($add, true) . ' at position ' . key($this->configuration) . BRLF;
		}
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * @return static
	 */
	public function read()
	{
		$this->configuration = (new Read($this->file_name))->read();
		return $this;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove sub-elements from a sub-section path
	 *
	 * @param $path   string[]
	 * @param $remove array
	 */
	public function remove(array $path, array $remove)
	{

	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * @return static
	 */
	public function write()
	{
		(new Write($this->file_name))->write();
		foreach ($this->included_files as $included_file) {
			$included_file->write();
		}
		$this->included_files = [];
		return $this;
	}

}
