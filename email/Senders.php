<?php
namespace ITRocks\Framework\Email;

use Exception;
use ITRocks\Framework\Email\Sender\Smtp;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;

/**
 * Multiple configurable email senders
 */
class Senders implements Configurable
{
	use Has_Get;

	//------------------------------------------------------------------------------------- TRANSPORT
	const TRANSPORT = 'transport';

	//-------------------------------------------------------------------------------------- $senders
	/**
	 * @var Sender[]
	 */
	public array $senders = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor of the Sender plugin stores the configuration into the object properties.
	 *
	 * @param $configuration array Sender configurations
	 * @throws Exception
	 */
	public function __construct(mixed $configuration = [])
	{
		if (!$configuration) {
			return;
		}
		foreach ($configuration as $identifier => $sender_configuration) {
			$transport = $sender_configuration[static::TRANSPORT] ?? Smtp::TRANSPORT;
			$this->senders[$identifier] = Sender::call($transport, $sender_configuration);
		}
	}

	//---------------------------------------------------------------------------------------- sender
	/**
	 * Get a Sender knowing its identifier
	 *
	 * @param $identifier string
	 * @return ?Sender null if identifier is not set in configuration
	 */
	public function sender(string $identifier) : ?Sender
	{
		return $this->senders[$identifier] ?? null;
	}

}
