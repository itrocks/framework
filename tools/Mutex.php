<?php
namespace ITRocks\Framework\Tools;

/**
 * Mutual exclusion management : these locks are local to your server
 */
class Mutex
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var resource
	 */
	protected mixed $file;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	protected string $file_name;

	//------------------------------------------------------------------------------------------ $key
	/**
	 * @var string
	 */
	protected string $key;

	//------------------------------------------------------------------------------------------ $own
	/**
	 * @var boolean
	 */
	public bool $own = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $key string
	 */
	public function __construct(string $key)
	{
		if (!is_dir('cache/locks')) {
			mkdir('cache/locks');
		}
		$this->key       = $key;
		$this->file_name = "cache/locks/$key.lock";
		$this->file      = fopen($this->file_name, 'w+');
	}

	//------------------------------------------------------------------------------------ __destruct
	public function __destruct()
	{
		$this->unlock();
	}

	//------------------------------------------------------------------------------------------ lock
	/**
	 * @param $blocking boolean if false, lock will not wait for file unlock
	 * @return boolean
	 */
	public function lock(bool $blocking = true) : bool
	{
		if (!flock($this->file, $blocking ? LOCK_EX : (LOCK_EX | LOCK_NB))) {
			return false;
		}
		ftruncate($this->file, 0);
		fwrite($this->file, getmypid());
		fflush($this->file);
		$this->own = true;
		return true;
	}

	//---------------------------------------------------------------------------------------- unlock
	/**
	 * @return boolean
	 */
	public function unlock() : bool
	{
		if (!$this->own) {
			return false;
		}
		ftruncate($this->file, 0);
		fflush($this->file);
		flock($this->file, LOCK_UN);
		$this->own = false;
		return true;
	}

}
