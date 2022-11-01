<?php
namespace ITRocks\Framework\Email;

use Exception;
use ITRocks\Framework\Email;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Tools\Names;

/**
 * Sends emails
 *
 * This offers a ITRocks interface to mail senders
 */
abstract class Sender implements Configurable, Sender_Interface
{
	use Has_Get;

	//----------------------------------------------------------------------- Configuration constants
	const BCC  = 'bcc';
	const FROM = 'from';
	const TO   = 'to';

	//------------------------------------------------------------------------------------------ $bcc
	/**
	 * Configuration of blind-carbon-copy email address enable to send every email sent by this
	 * feature to a given addresses list.
	 *
	 * @var string|string[]
	 */
	public array|string $bcc;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * Use this to force sender to this one, whatever is the sending address mail coming from the
	 * application.
	 *
	 * @var string|string[]
	 */
	public array|string $from;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * Use this to override all to, cc, bcc recipients and replace them with these recipients only.
	 * Configuration of this property is recommended in development environment to avoid sending
	 * emails to production recipients when you test your application.
	 *
	 * @var string|string[]
	 */
	public array|string $to;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor of the Sender plugin stores the configuration into the object properties.
	 *
	 * @param $configuration string[]
	 */
	public function __construct(mixed $configuration = [])
	{
		if ($configuration) {
			if (isset($configuration[self::BCC]))  $this->bcc  = $configuration[self::BCC];
			if (isset($configuration[self::FROM])) $this->from = $configuration[self::FROM];
			if (isset($configuration[self::TO]))   $this->to   = $configuration[self::TO];
		}
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * Factory used to create a specialized sender
	 *
	 * @param $transport     string @example 'smtp'
	 * @param $configuration string[]
	 * @return Sender
	 * @throws Exception
	 */
	public static function call(string $transport, array $configuration = []) : Sender
	{
		$transport_class_name = static::class . BS . Names::propertyToClass($transport);
		if (class_exists($transport_class_name)) {
			return new $transport_class_name($configuration);
		}
		else {
			throw new Exception("Class $transport_class_name not found");
		}
	}

	//------------------------------------------------------------------------------------------ send
	/**
	 * Send an email using its account connection information
	 * or the default SMTP account configuration.
	 *
	 * @param $email Email
	 * @return boolean|string true if sent, error message if string
	 */
	abstract public function send(Email $email) : bool|string;

	//----------------------------------------------------------------------------- sendConfiguration
	/**
	 * Configure email send process : prepares the email recipients
	 *
	 * @param $email Email email account is used, email recipients may be changed by the configuration
	 */
	protected function sendConfiguration(Email $email)
	{
		// force sender : all mails coming from the application will use this sender (from)
		if (isset($this->from)) {
			if (!is_array($this->from)) {
				$this->from = [$this->from];
			}
			foreach ($this->from as $from_name => $from_email) {
				$email->from = new Recipient($from_email, is_numeric($from_name) ? null : $from_name);
			}
		}
		// development / test parameters to override 'To' and/or 'Bcc' headers
		if (isset($this->to)) {
			$email->blind_copy_to = [];
			$email->copy_to       = [];
			$email->to            = [];
			if (!is_array($this->to)) {
				$this->to = [$this->to];
			}
			foreach ($this->to as $to_name => $to_email) {
				$email->to[] = new Recipient($to_email, is_numeric($to_name) ? null : $to_name);
			}
		}
		// bcc is useful in production too
		if (isset($this->bcc)) {
			if (!is_array($this->bcc)) {
				$this->bcc = [$this->bcc];
			}
			foreach ($this->bcc as $bcc) {
				$email->blind_copy_to[] = new Recipient($bcc);
			}
		}
	}

}
