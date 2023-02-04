<?php
namespace ITRocks\Framework\PHP\Dependency\Repository;

use ITRocks\Framework\PHP\Dependency\Repository;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

#[Extend(Repository::class)]
trait Get_Data
{

	//----------------------------------------------------------------------------------- fileClasses
	/**
	 * @param $file_name string
	 * @return string[]
	 */
	public function fileClasses(string $file_name) : array
	{
		return json_decode(
			file_get_contents($this->cacheFileName($file_name, 'file')),
			JSON_OBJECT_AS_ARRAY
		)['class']
			?? [];
	}

	//------------------------------------------------------------------------------- getFileContents
	/** @return string[] */
	public function getFileContents() : array
	{
		$files = $this->getFiles();
		if (count($this->file_contents) === count($files)) {
			return $this->file_contents;
		}
		foreach ($files as $file) {
			$file = "$this->home/$file";
			if (!isset($this->file_contents[$file])) {
				$this->file_contents[$file] = file_get_contents($file);
			}
		}
		return $this->file_contents;
	}

	//-------------------------------------------------------------------------------------- getFiles
	/** @return string[] */
	public function getFiles() : array
	{
		return $this->files
			?: ($this->files = json_decode(file_get_contents($this->cacheFileName('files'))));
	}

}
