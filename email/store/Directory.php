<?php
namespace ITRocks\Framework\Email\Store;

use ITRocks\Framework\Traits\Has_Name;

/**
 * Email store directory
 *
 * @representative full_path
 * @store_name email_directories
 */
class Directory
{
	use Has_Name;

	//------------------------------------------------------------------------------------ $full_path
	/**
	 * @getter
	 * @var string
	 */
	public string $full_path;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @link Object
	 * @setter
	 * @var ?Directory
	 */
	public ?Directory $parent = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $full_path string|null
	 */
	public function __construct(string $full_path = null)
	{
		if (isset($full_path)) {
			if (str_contains($full_path, SL)) {
				$this->name   = rLastParse($full_path, SL);
				$this->parent = new Directory(lLastParse($full_path, SL));
			}
			else {
				$this->name = $full_path;
			}
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->getFullPath();
	}

	//----------------------------------------------------------------------------------- getFullPath
	/**
	 * @return string
	 */
	protected function getFullPath() : string
	{
		if (!isset($this->full_path)) {
			$this->full_path = $this->parent
				? ($this->parent->getFullPath() . SL . $this->name)
				: $this->name;
		}
		return $this->full_path;
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * @param $parent Directory|null
	 */
	protected function setParent(Directory $parent = null) : void
	{
		unset($this->full_path);
		$this->parent = $parent;
	}

}
