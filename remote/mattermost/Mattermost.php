<?php
namespace ITRocks\Framework\Remote;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Remote\Mattermost\Exception;
use stdClass;

/**
 * Plugin to post to mattermost
 */
class Mattermost implements Configurable
{

	//--------------------------------------------------------------------------------------- CHANNEL
	const CHANNEL = 'MATTERMOST_CHANNEL';

	//------------------------------------------------------------------------------------------ HOOK
	const HOOK = 'MATTERMOST_HOOK';

	//-------------------------------------------------------------------------------------- USERNAME
	const USERNAME = 'MATTERMOST_USERNAME';

	//-------------------------------------------------------------------------------------- $channel
	/**
	 * Mattermost default channel id
	 *
	 * @var string
	 */
	public string $channel;

	//----------------------------------------------------------------------------------------- $hook
	/**
	 * Mattermost incoming web hook
	 *
	 * @var string
	 */
	public string $hook;

	//------------------------------------------------------------------------------------- $username
	/**
	 * Username of the post
	 *
	 * @var string
	 */
	public string $username;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Mattermost_Connector configuration
	 *
	 * @param $configuration array
	 * @throws Exception
	 */
	public function __construct($configuration = [])
	{
		$this->channel  = $configuration[static::CHANNEL];
		$this->hook     = $configuration[static::HOOK];
		$this->username = $configuration[static::USERNAME];

		foreach (get_object_vars($this) as $property => $value) {
			if (empty($value)) {
				throw new Exception('Missing required property ' . $property);
			}
		}
	}

	//------------------------------------------------------------------------------------------ post
	/**
	 * Posts a text message to a mattermost channel
	 *
	 * @param $message  string
	 * @param $channel  string|null
	 * @param $username string|null
	 * @return bool|string
	 */
	function post(string $message, string $channel = null, string $username = null) : bool|string
	{
		$content           = new stdClass();
		$content->username = $username ?? $this->username;
		$content->channel  = $channel ?? $this->channel;
		$content->text     = $message;

		$content = json_encode($content);
		$ch      = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->hook);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt(
			$ch, CURLOPT_HTTPHEADER,
			['Content-Type: application/json', 'Content-Length: ' . strlen($content)]
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch);
		return $server_output;
	}

}
