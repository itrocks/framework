<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;

/**
 * Multiple configurable email senders
 */
class Senders implements Configurable
{
	use Has_Get;

	//-------------------------------------------------------------------------------------- $senders
	/**
	 * @var Sender[]
	 */
	public $senders;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor of the Sender plugin stores the configuration into the object properties.
	 *
	 * @param $configuration array Sender configurations
	 */
	public function __construct($configuration = [])
	{
		if ($configuration) {
			foreach ($configuration as $identifier => $sender_configuration) {
				$this->senders[$identifier] = new Sender($sender_configuration);
			}
		}
	}

	//---------------------------------------------------------------------------------------- sender
	/**
	 * Get a Sender knowing its identifier
	 *
	 * @param $identifier string
	 * @return Sender|null null if identifier is not set in configuration
	 */
	public function sender($identifier)
	{
		return isset($this->senders[$identifier]) ? $this->senders[$identifier] : null;
	}

}
