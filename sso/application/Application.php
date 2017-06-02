<?php
namespace ITRocks\Framework\SSO;

/**
 * Application where user can connect through SSO
 *
 */
class Application
{

	//------------------------------------------ Authentication_Server plugin configuration constants
	const MAX_SESSION_TIME = 'max_session_time';
	const NAME             = 'name';
	const REDIRECT         = 'redirect';
	const SENTENCE         = 'sentence';
	const URI              = 'uri';

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
	 * Configuration keys can be max_session_time, name, redirect, sentence, uri
	 *
	 * @param $configuration array
	 */
	public function __construct($configuration = [])
	{
		foreach ($configuration as $property_name => $value) {
			$this->$property_name = $value;
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
		return $sentence && ($this->sentence === $sentence);
	}

	//--------------------------------------------------------------------------------------- isValid
	/**
	 * returns true if application is valid and can be used
	 *
	 * @return string
	 */
	public function isValid()
	{
		return $this->name && $this->sentence && $this->uri;
	}

}
