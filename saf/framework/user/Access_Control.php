<?php
namespace SAF\Framework\User;

use SAF\Framework\Controller\Main;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\User;

/**
 * Very simple user access control plugin :
 * runController() can be called only if a user is authenticated
 *
 * A list of free access URI can be given as a configuration
 */
class Access_Control implements Configurable, Registerable
{

	/**
	 * @var string[]
	 */
	public $exceptions = [
		'/.*/.*/User/authenticate',
		'/.*/.*/User/login',
		'/.*/.*/Menu/output',
		'/SAF/Framework/Tests/run'
	];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			$this->exceptions = array_merge($this->exceptions, $configuration);
		}
	}

	//------------------------------------------------------------------------------------- checkUser
	/**
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
	 * @param $files array
	 */
	public function checkUser(&$uri, &$get, &$post, &$files)
	{
		if (!User::current() && !$this->exception($uri)) {
			$uri = '/SAF/Framework/User/login';
			$_get = [];
			if (isset($get['as_widget']))   $_get['as_widget']   = true;
			if (isset($get['is_included'])) $_get['is_included'] = true;
			$get   = $_get;
			$post  = [];
			$files = [];
		}
	}

	//------------------------------------------------------------------------------------- exception
	/**
	 * Returns true if there is a set exception, eg no access control for this URI
	 *
	 * @param $uri string
	 * @return boolean
	 */
	private function exception($uri)
	{
		// could use preg_grep, but I don't want to ask delimiters into exceptions array
		foreach ($this->exceptions as $exception) {
			if (preg_match('%^' . $exception . '$%', $uri)) {
				return true;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->beforeMethod([Main::class, 'runController'], [$this, 'checkUser']);
	}

}
