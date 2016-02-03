<?php
namespace SAF\Framework\User;

use SAF\Framework\Application;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Tools\Names;
use SAF\Framework\User;
use SAF\Framework\User\Group\Has_Groups;
use SAF\Framework\View;
use SAF\Framework\Widget\Button;
use SAF\Framework\Widget\Menu;
use SAF\Framework\Widget\Menu\Item;

/**
 * Very simple user access control plugin :
 * - runController() can be called only if a user is authenticated
 * - runController() can be called only if the user has access to the uri (low-level features)
 *   This control is done only if isA(User, Has_Groups)
 *
 *
 * A list of free access URI can be given as a configuration
 */
class Access_Control implements Configurable, Registerable
{

	//---------------------------------------- User access control configuration array keys constants
	const ALL_USERS  = 'all_users';
	const BLANK      = 'blank';
	const EXCEPTIONS = 'exceptions';

	//------------------------------------------------------------------------------------ $all_users
	/**
	 * Acces to these features is granted for all logged-in users,
	 * even if they have no explicit access to them
	 *
	 * @var array
	 */
	public $all_users = [
		'/',
		'/.*/.*/Menu/output',
		'/.*/.*/User/disconnect'
	];

	//---------------------------------------------------------------------------------------- $blank
	/**
	 * These features will display blank code if no access is granted (no user / no access)
	 *
	 * @var string[]
	 */
	public $blank = [
		'/.*/.*/Environment/output',
		'/.*/.*/Menu/output'
	];

	//----------------------------------------------------------------------------------- $exceptions
	/**
	 * Access to these features is always granted, even if no user is connected
	 * or if he has no explicit access to them
	 *
	 * @var string[]
	 */
	public $exceptions = [
		'/.*/.*/Tests/run',
		'/.*/.*/User/authenticate',
		'/.*/.*/User/login',
		'/.*/.*/Webservice/authenticate'
	];

	//-------------------------------------------------------------------------------------- $protect
	/**
	 * @var boolean
	 */
	private static $protect = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration[self::ALL_USERS])) {
			$this->all_users = array_merge($this->all_users, $configuration[self::ALL_USERS]);
		}
		if (isset($configuration[self::BLANK])) {
			$this->blank = array_merge($this->blank, $configuration[self::BLANK]);
		}
		if (isset($configuration[self::EXCEPTIONS])) {
			$this->exceptions = array_merge($this->exceptions, $configuration[self::EXCEPTIONS]);
		}
	}

	//-------------------------------------------------------------------------------------- allUsers
	/**
	 * Returns true if the uri may be accessed by all users (user-exception)
	 *
	 * @param $uri string
	 * @return boolean
	 */
	private function allUsers($uri)
	{
		// could use preg_grep, but I don't want to ask delimiters into exceptions array
		foreach ($this->all_users as $exception) {
			if (preg_match('%^' . $exception . '$%', $uri)) {
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------------------- checkAccess
	/**
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
	 * @param $files array
	 */
	public function checkAccess(&$uri, &$get, &$post, &$files)
	{
		$origin_uri = $uri;
		$this->checkUser($uri, $get, $post, $files);
		// TODO HIGHEST (@ next deployment) replace with if ($this->checkUser)
		if (($origin_uri === $uri) && isA(User::current(), Has_Groups::class)) {
			$this->checkFeatures($uri, $get, $post, $files);
		}
	}

	//----------------------------------------------------------------------------- checkAccessToLink
	/**
	 * @param $result string The link (result of View::link())
	 */
	public function checkAccessToLink(&$result)
	{
		if (!self::$protect) {
			$user = User::current();
			if ($user && isA($user, Has_Groups::class)) {
				$nop = [];
				if (!$this->checkFeatures($result, $nop, $nop, $nop)) {
					$result = null;
				}
			}
		}
	}

	//------------------------------------------------------------------------- checkAccessToMenuItem
	/**
	 * @param $result Item
	 */
	public function checkAccessToMenuItem(&$result)
	{
		if (isset($result)) {
			$user = User::current();
			if ($user && isA($user, Has_Groups::class)) {
				$nop = [];
				if (!$this->checkFeatures($result->link, $nop, $nop, $nop)) {
					$result = null;
				}
			}
		}
	}

	//--------------------------------------------------------------------------------- checkFeatures
	/**
	 * @param $uri   string must start with '/' @example /SAF/Framework/User/add
	 * @param $get   array
	 * @param $post  array
	 * @param $files array
	 * @return boolean
	 */
	private function checkFeatures(&$uri, &$get, &$post, &$files)
	{
		$last_protect = self::$protect;
		self::$protect = true;
		$user = User::current();
		$accessible = true;
		/** @var $user User|Has_Groups */
		if (
			isA($user, Has_Groups::class)
			&& !$user->hasAccessTo($this->cleanupUri($uri))
			&& !$this->allUsers($uri)
		) {
			if ($this->isBlank($uri)) {
				$this->setUri(
					View::link(Application::class, Feature::F_BLANK), $uri, $get, $post, $files
				);
				$accessible = false;
			}
			elseif (!$this->exception($uri)) {
				$this->setUri(
					View::link(Access_Control::class, Feature::F_DENIED), $uri, $get, $post, $files
				);
				$accessible = false;
			}
		}
		self::$protect = $last_protect;
		return $accessible;
	}

	//------------------------------------------------------------------------------------- checkUser
	/**
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
	 * @param $files array
	 * @todo HIGHEST private (@ next deployment) + return false if no user is logged in
	 */
	public function checkUser(&$uri, &$get, &$post, &$files)
	{
		if (!User::current()) {
			if ($this->isBlank($uri)) {
				$this->setUri(
					View::link(Application::class, Feature::F_BLANK), $uri, $get, $post, $files
				);
			}
			elseif (!$this->exception($uri)) {
				$this->setUri(
					View::link(User::class, Feature::F_LOGIN), $uri, $get, $post, $files
				);
			}
		}
	}

	//------------------------------------------------------------------------------------ cleanupUri
	/**
	 * Change a full-featured uri to something simple (/Path/Class/Feature)
	 *
	 * @example '/SAF/Framework/Property/18/select/Bappli/Sfkgroup/Claims'
	 * will become '/SAF/Framework/Property/select'
	 * @example '/SAF/Framework/Users' will become '/SAF/Framework/User/listData'
	 * @param $uri string
	 * @return string
	 */
	private function cleanupUri($uri)
	{
		$uri = new Uri($uri);
		return View::link(Names::setToClass($uri->controller_name, false), $uri->feature_name);
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

	//--------------------------------------------------------------------------------------- isBlank
	/**
	 * Returns true if this URI must show the application blank page if no user is connected
	 *
	 * @param $uri string
	 * @return boolean
	 */
	private function isBlank($uri)
	{
		// could use preg_grep, but I don't want to ask delimiters into blank array
		foreach ($this->blank as $blank) {
			if (preg_match('%^' . $blank . '$%', $uri)) {
				return true;
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------- menuCheckAccess
	/**
	 * @return boolean|null null if should call the original method, false to simply return false
	 */
	public function menuCheckAccess()
	{
		return User::current() ? null : false;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Main::class, 'runController'], [$this, 'checkAccess']);
		$aop->beforeMethod([Menu::class, 'constructBlock'], [$this, 'menuCheckAccess']);
		$aop->afterMethod([View::class, 'link'], [$this, 'checkAccessToLink']);
		$aop->afterMethod([Menu::class, 'constructItem'], [$this, 'checkAccessToMenuItem']);
		$aop->beforeMethod([View::class, 'run'], [$this, 'removeButtonsWithNoLink']);
	}

	//----------------------------------------------------------------------- removeButtonsWithNoLink
	/**
	 * Remove buttons which link is empty (eg due to access control limitation)
	 *
	 * @param $parameters Button[]
	 */
	public function removeButtonsWithNoLink(&$parameters)
	{
		foreach (['general_buttons', 'selection_buttons'] as $buttons) {
			if (isset($parameters[$buttons])) {
				foreach ($parameters[$buttons] as $key => $button) {
					if (empty($button->link)) {
						unset($parameters[$buttons][$key]);
					}
				}
			}
		}
	}

	//---------------------------------------------------------------------------------------- setUri
	/**
	 * Sets the new uri, reset get/post/files parameters
	 *
	 * @param $new_uri string
	 * @param $uri     string
	 * @param $get     array
	 * @param $post    array
	 * @param $files   array
	 */
	private function setUri($new_uri, &$uri, &$get, &$post, &$files)
	{
		$uri = $new_uri;
		$_get = [];
		if (isset($get[Parameter::AS_WIDGET]))   $_get[Parameter::AS_WIDGET]   = true;
		if (isset($get[Parameter::IS_INCLUDED])) $_get[Parameter::IS_INCLUDED] = true;
		$get   = $_get;
		$post  = [];
		$files = [];
	}

}
