<?php
namespace ITRocks\Framework\SSO;

/**
 * Application where user can connect through SSO
 *
 */
class Application
{
	//------------------------------------------------------------------------------ MAX_SESSION_TIME
	const MAX_SESSION_TIME = 'max_session_time';

	//------------------------------------------------------------------------------------------ NAME
	const NAME = 'name';

	//-------------------------------------------------------------------------------------- REDIRECT
	const REDIRECT = 'redirect';

	//-------------------------------------------------------------------------------------- SENTENCE
	const SENTENCE = 'sentence';

	//------------------------------------------------------------------------------------------- URI
	const URI = 'uri';

	//----------------------------------------------------------------------------- $max_session_time
	/**
	 * The maximum timeout for a session for application (if user has not specifically disconnect)
	 * in seconds
	 *
	 * @example 600
	 * @var string
	 */
	public $max_session_time;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The name of application
	 *
	 * @example stats
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------- $redirect
	/**
	 * The path where to redirect after application has validated authentication
	 *
	 * @example /home
	 * @var string
	 */
	public $redirect;

	//------------------------------------------------------------------------------------- $sentence
	/**
	 * The sentence sent to application to recognize the authentication server
	 *
	 * @example This is the kindly deadly incredible sentence of security!
	 * @var string
	 */
	private $sentence;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * The URL of application where to send authentication parameters
	 *
	 * @example http://sebastien.meudec.com/my_path_to_get_token
	 * @var string
	 */
	public $uri;

	//----------------------------------------------------------------------------------- __construct
	/**
	 *
	 * @param $configuration array|null
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration[self::MAX_SESSION_TIME])) {
			$this->max_session_time = $configuration[self::MAX_SESSION_TIME];
		}
		if (isset($configuration[self::NAME])) {
			$this->name = $configuration[self::NAME];
		}
		if (isset($configuration[self::REDIRECT])) {
			$this->redirect= $configuration[self::REDIRECT];
		}
		if (isset($configuration[self::SENTENCE])) {
			$this->sentence = $configuration[self::SENTENCE];
		}
		if (isset($configuration[self::URI])) {
			$this->uri = $configuration[self::URI];
		}
	}

	//----------------------------------------------------------------------------------- hasSentence
	/**
	 * returns if the application has the given sentence
	 *
	 * @param $sentence string
	 * @return boolean
	 */
	public function hasSentence($sentence)
	{
		return (!empty($sentence) && ($this->sentence === $sentence));
	}

	//--------------------------------------------------------------------------------------- isValid
	/**
	 * returns true if application is valid and can be used
	 *
	 * @return string
	 */
	public function isValid()
	{
		return !empty($this->name) && !empty($this->uri) && !empty($this->sentence);
	}

}
