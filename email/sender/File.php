<?php
namespace ITRocks\Framework\Email\Sender;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Email\Sender;

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
	public $path;

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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $email Email
	 * @return boolean|string
	 */
	public function send(Email $email)
	{
		// mime encode of email (for html, images and attachments)
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$encoder = Builder::create(Encoder::class, [$email]);
		$content = $encoder->encode();
		$headers = $encoder->encodeHeaders();

		if (!is_dir($this->path)) {
			mkdir($this->path, 0700);
			chmod($this->path, 0700);
		}

		$file_path = $this->path . SL . str_replace(DOT, '-', uniqid('', true)) . '.eml';
		file_put_contents($file_path, $headers . LF . LF . $content);

		return true;
	}

}
