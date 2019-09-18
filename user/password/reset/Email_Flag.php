<?php
namespace ITRocks\Framework\User\Password\Reset;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\User\Password\Reset;

/**
 * This plugin allow another program to send password reset emails asynchronously
 *
 * Each time a password reset email is generated, and empty flag file $file_path is created
 * The other program has to scan all applications for emails without account to send, and send them
 * It can call /ITRocks/Framework/Email/xxx/content to easily get the email content
 */
class Email_Flag implements Configurable, Registerable
{

	//------------------------------------------------------------------------------------ $file_path
	/**
	 * @var string
	 */
	public $file_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string|string[]
	 */
	public function __construct($configuration = null)
	{
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
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Reset::class, 'sendEmail'], [$this, 'touchFlag']);
	}

	//------------------------------------------------------------------------------------- touchFlag
	public function touchFlag()
	{
		if ($this->file_path) {
			touch($this->file_path);
		}
	}

}
