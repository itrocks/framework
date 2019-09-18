<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Email;

/**
 * Email file tools
 */
class File
{

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var Email
	 */
	public $email;

	//------------------------------------------------------------------------------------- $filename
	/**
	 * @var string
	 */
	public $filename;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Can be initialized with a filename or an Email object
	 *
	 * @param $email string|Email
	 */
	public function __construct($email)
	{
		if ($email instanceof Email) {
			$this->email = $email;
		}
		elseif (is_file($email)) {
			$this->filename = $email;
		}
		else {
			user_error('Need a valid email or filename', E_USER_ERROR);
		}
	}

	//----------------------------------------------------------------------------------------- email
	/**
	 * @return Email
	 */
	public function email()
	{
		if ($this->email) {
			return $this->email;
		}
		return $this->email = (new Decoder)->decodeFile($this->filename);
	}

	//------------------------------------------------------------------------------------------ file
	/**
	 * @param $directory string The directory where to store the file into
	 * @return string The full path of the generated file
	 */
	public function file($directory)
	{
		if ($this->filename) {
			return $this->filename;
		}
		// TODO use Sender\File when merged
		return '';
	}

}
