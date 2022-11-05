<?php
namespace ITRocks\Framework\User\Password\Reset;

use ITRocks\Framework\Email\Sender\File;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * This plugin allow another program to send password reset emails asynchronously
 *
 * Each time a password reset email is generated, and empty flag file $file_path is created
 * The other program has to scan all applications for emails without account to send, and send them
 * It can call /ITRocks/Framework/Email/xxx/content to easily get the email content
 *
 * TODO NORMAL This plugin should be moved into Email
 */
class Email_Flag implements Configurable, Registerable
{

	//------------------------------------------------------------------------------------ $file_path
	/**
	 * @var string
	 */
	public string $file_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string|string[]
	 */
	public function __construct(mixed $configuration = null)
	{
		if (!$configuration) {
			return;
		}
		if (is_string($configuration)) {
			$configuration = ['file_path' => $configuration];
		}
		foreach ($configuration as $property_name => $value) {
			$this->$property_name = $value;
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod([File::class, 'send'], [$this, 'touchFlag']);
	}

	//------------------------------------------------------------------------------------- touchFlag
	public function touchFlag() : void
	{
		if ($this->file_path) {
			touch($this->file_path);
		}
	}

}
