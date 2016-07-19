<?php
namespace SAF\Framework\SSO;

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

	//------------------------------------------------------------------------------------------- URI
	const URI = 'uri';

	//-------------------------------------------------------------------------------------- SENTENCE
	const SENTENCE = 'sentence';

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The maximum timeout for a session for application (if user has not specifically disconnect)
	 * @var string
	 */
	public $max_session_time;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The name of application
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------- $redirect
	/**
	 * The path where to redirect after application has validated authentication
	 * @var string
	 */
	public $redirect;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * The home url of application where to redirect on 1st access
	 * @var string
	 */
	public $uri;

	//------------------------------------------------------------------------------------- $sentence
	/**
	 * The sentence sent to application to recognize the authentication server
	 * @var string
	 */
	private $sentence;

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
		if (isset($configuration[self::URI])) {
			$this->uri = $configuration[self::URI];
		}
		if (isset($configuration[self::SENTENCE])) {
			$this->sentence = $configuration[self::SENTENCE];
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
	 * @return string
	 */
	public function isValid()
	{
		return !empty($this->name) && !empty($this->uri) && !empty($this->sentence);
	}

}
