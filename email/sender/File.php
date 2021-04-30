<?php
namespace ITRocks\Framework\Email\Sender;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Email\Sender;
use ReflectionException;

/**
 * Sends an email to a file
 */
class File extends Sender
{

	//----------------------------------------------------------------------------------------- $path
	/**
	 * The directory path where the email MIME files will be written when sent
	 *
	 * @var string
	 */
	public string $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpMissingParentConstructorInspection No parent call
	 * @param $configuration string[]|integer[]
	 */
	public function __construct($configuration = [])
	{
		if (is_string($configuration)) {
			$configuration = ['path' => $configuration];
		}
		if (isset($configuration['path'])) {
			$this->path = $configuration['path'];
		}
	}

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $email Email
	 * @return boolean
	 * @throws ReflectionException
	 */
	public function send(Email $email): bool
	{
		$encoder = Builder::create(Encoder::class, [$email]);
		$message = $encoder->toString();

		if (!is_dir($this->path)) {
			mkdir($this->path, 0700);
			chmod($this->path, 0700);
		}

		$file_path = $this->path . SL . str_replace(DOT, '-', uniqid('', true)) . '.eml';
		file_put_contents($file_path, $message);

		return true;
	}

}
