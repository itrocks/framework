<?php
namespace ITRocks\Framework\Email;

use Exception;
use ITRocks\Framework\Email;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use Swift_Transport;

/**
 * Sends emails
 *
 * This offers a ITRocks interface to the SwiftMailer package
 */
abstract class Sender implements Configurable, Sender_Interface
{
	use Has_Get;

	//----------------------------------------------------------------------- Configuration constants
	const BCC      = 'bcc';
	const TO       = 'to';

	//------------------------------------------------------------------------------------------ $bcc
	/**
	 * Configuration of blind-carbon-copy email address enable to send every email sent by this
	 * feature to a given addresses list.
	 *
	 * @var string|string[]
	 */
	public string|array $bcc;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * Use this to override all to, cc, bcc recipients and replace them with these recipients only.
	 * Configuration of this property is recommended in development environment to avoid sending
	 * emails to production recipients when you test your application.
	 *
	 * @var string|string[]
	 */
	public string|array $to;

	//------------------------------------------------------------------------------------ $transport
	/**
	 * Transport used to send the mail
	 */
	public Swift_Transport $transport;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor of the Sender plugin stores the configuration into the object properties.
	 *
	 * @param $configuration string[]
	 */
	public function __construct($configuration = [])
	{
		if ($configuration) {
			if (isset($configuration[self::BCC])) $this->bcc = $configuration[self::BCC];
			if (isset($configuration[self::TO]))  $this->to  = $configuration[self::TO];
		}
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * Factory used to create a specialized sender
	 *
	 * @param $transport string
	 * @param $sender_configuration string[]
	 * @return Sender
	 * @throws Exception
	 */
	public static function call(string $transport, array $sender_configuration = []): Sender
	{
		// Ensure we have a valid classname
		$transport = ucfirst(strtolower($transport));
		// We need to fully qualify with the namespace to load the class
		$transport_class = __NAMESPACE__ . BS . 'Sender' . BS . $transport;
		if (class_exists($transport_class)) {
			return new $transport_class($sender_configuration);
		} else {
			throw new Exception("Class $transport_class not found");
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
	abstract public function send(Email $email): bool|string;

	//----------------------------------------------------------------------------- sendConfiguration
	/**
	 * Configure email send process : prepares the email recipients
	 *
	 * @param $email Email email account is used, email recipients may be changed by the configuration
	 */
	protected function sendConfiguration(Email $email): void
	{
		// dev / pre-production parameters to override 'To' and/or 'Bcc' headers
		if (isset($this->to)) {
			$email->blind_copy_to = [];
			$email->copy_to       = [];
			$email->to            = [];
			if (!is_array($this->to)) {
				$this->to = [$this->to];
			}
			foreach ($this->to as $to_name => $to_email) {
				array_push($email->to, new Recipient($to_email, is_numeric($to_name) ? null : $to_name));
			}
		}
		if (isset($this->bcc)) {
			if (!is_array($this->bcc)) {
				$this->bcc = [$this->bcc];
			}
			foreach ($this->bcc as $bcc) {
				array_push($email->blind_copy_to, new Recipient($bcc));
			}
		}
	}

}
