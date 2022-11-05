<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;

/**
 * The local_access.php configuration file
 */
class Local_Access extends File
{

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var string[]
	 */
	public array $lines = [];

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $local_access string Feature path
	 */
	public function add(string $local_access) : void
	{
		$this->lines[] = $local_access;
		sort($this->lines);
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read() : void
	{
		(new Local_Access\Reader($this))->read();
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $local_access string Feature path
	 */
	public function remove(string $local_access) : void
	{
		$key = array_search($local_access, $this->lines);
		if ($key !== false) {
			unset($this->lines[$key]);
		}
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write() : void
	{
		(new Local_Access\Writer($this))->write();
	}

}
