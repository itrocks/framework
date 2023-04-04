<?php
namespace ITRocks\Framework\Email\Store;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Email store directory
 */
#[Representative('full_path'), Store('email_directories')]
class Directory
{
	use Has_Name;

	//------------------------------------------------------------------------------------ $full_path
	#[Getter]
	public string $full_path = '';

	//--------------------------------------------------------------------------------------- $parent
	#[Setter]
	public ?Directory $parent = null;

	//----------------------------------------------------------------------------------- __construct
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
	public function __toString() : string
	{
		return $this->getFullPath();
	}

	//----------------------------------------------------------------------------------- getFullPath
	protected function getFullPath() : string
	{
		if (isset($this->full_path)) {
			return $this->full_path;
		}
		return $this->full_path = $this->parent
			? ($this->parent->getFullPath() . SL . $this->name)
			: $this->name;
	}

	//------------------------------------------------------------------------------------- setParent
	/** @noinspection PhpUnused #Setter */
	protected function setParent(Directory $parent = null) : void
	{
		unset($this->full_path);
		$this->parent = $parent;
	}

}
