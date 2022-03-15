<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Application;
use ITRocks\Framework\Email;

/**
 * Email file tools
 */
class File
{

	//---------------------------------------------------------------------------------------- $email
	/**
	 * @var ?Email
	 */
	protected ?Email $email = null;

	//--------------------------------------------------------------------------------- $file_content
	/**
	 * @var ?string Email file content
	 */
	protected ?string $file_content = null;
	
	//------------------------------------------------------------------------------------- $filename
	/**
	 * @var ?string File name
	 */
	protected ?string $filename = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Can be initialized with a filename, an email file content, or an Email object
	 *
	 * @param $email string|Email
	 */
	public function __construct(string|Email $email)
	{
		if ($email instanceof Email) {
			$this->email = $email;
		}
		elseif (is_file($email)) {
			$this->filename = $email;
		}
		else {
			$this->file_content = $email;
		}
	}

	//----------------------------------------------------------------------------------------- email
	/**
	 * @return Email
	 */
	public function email() : Email
	{
		if ($this->file_content && !$this->filename) {
			$this->filename = Application::current()->getTemporaryFilesPath()
				. SL . uniqid('', true) . '.eml';
			file_put_contents($this->filename, $this->file_content);
			$temporary = true;
		}
		if ($this->filename && !$this->email) {
			$this->email = (new Decoder)->decodeFile($this->filename);
			if (isset($temporary)) {
				unlink($this->filename);
				$this->filename = null;
			}
		}
		return $this->email;
	}

}
