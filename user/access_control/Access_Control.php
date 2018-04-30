<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Application;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Group\Feature;
use ITRocks\Framework\User\Group\Has_Groups;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Button\Has_General_Buttons;
use ITRocks\Framework\Widget\Button\Has_Selection_Buttons;
use ITRocks\Framework\Widget\Menu;
use ITRocks\Framework\Widget\Menu\Item;

/**
 * Very simple user access control plugin :
 * - runController() can be called only if a user is authenticated
 * - runController() can be called only if the user has access to the uri (low-level features)
 *   This control is done only if isA(User, Has_Groups)
 *
 * A list of free access URI can be given as a configuration
 */
class Access_Control implements Configurable, Registerable
{
	use Has_Get;

	//------------------------------------------------------------------------------------- ALL_USERS
	const ALL_USERS = 'all_users';

	//----------------------------------------------------------------------------------------- BLANK
	const BLANK = 'blank';

	//------------------------------------------------------------------------------------ EXCEPTIONS
	const EXCEPTIONS = 'exceptions';

	//------------------------------------------------------------------------------------ $all_users
	/**
	 * Access to these features is granted for all logged-in users,
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
	public function __construct($configuration = [])
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
	 * @param $files array[]
	 */
	public function checkAccess(&$uri, array &$get = [], array &$post = [], array &$files = [])
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
				list($uri, $arguments) = strpos($result, '?') ? explode('?', $result, 2) : [$result, null];
				if ($this->checkFeatures($uri)) {
					$result = $uri;
					if (!is_null($arguments)) {
						$result .= '?' . $arguments;
					}
				}
				else {
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
				if (!$this->checkFeatures($result->link)) {
					$result = null;
				}
			}
		}
	}

	//--------------------------------------------------------------------------------- checkFeatures
	/**
	 * @param $uri   string must start with '/' @example /ITRocks/Framework/User/add
	 * @param $get   array
	 * @param $post  array
	 * @param $files array[]
	 * @return boolean
	 */
	private function checkFeatures(&$uri, array &$get = [], array &$post = [], array &$files = [])
	{
		$last_protect  = self::$protect;
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
					View::link(Application::class, Controller\Feature::F_BLANK), $uri, $get, $post, $files
				);
				$accessible = false;
			}
			elseif (!pregMatchArray($this->exceptions, $uri)) {
				$this->setUri(
					View::link(Access_Control::class, Controller\Feature::F_DENIED), $uri, $get, $post, $files
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
	 * @param $files array[]
	 * @todo HIGHEST private (@ next deployment) + return false if no user is logged in
	 */
	public function checkUser(&$uri, array &$get, array &$post, array &$files)
	{
		if (!User::current()) {
			if ($this->isBlank($uri)) {
				$this->setUri(
					View::link(Application::class, Controller\Feature::F_BLANK), $uri, $get, $post, $files
				);
			}
			elseif (!pregMatchArray($this->exceptions, $uri)) {
				$this->setUri(
					View::link(User::class, Controller\Feature::F_LOGIN), $uri, $get, $post, $files
				);
			}
		}
	}

	//------------------------------------------------------------------------------------ cleanupUri
	/**
	 * Change a full-featured uri to something simple (/Path/Class/Feature)
	 *
	 * @example '/ITRocks/Framework/Property/18/select/Bappli/Sfkgroup/Claims'
	 * will become '/ITRocks/Framework/Property/select'
	 * @example '/ITRocks/Framework/Users' will become '/ITRocks/Framework/User/listData'
	 * @param $uri string
	 * @return string
	 */
	private function cleanupUri($uri)
	{
		$uri = new Uri($uri);
		return View::link(Names::setToClass($uri->controller_name, false), $uri->feature_name);
	}

	//----------------------------------------------------------------------------------- hasAccessTo
	/**
	 * Call this to know if an object|class has access to a feature
	 *
	 * @param $callable array|callable
	 * @return boolean
	 */
	public function hasAccessTo(array $callable)
	{
		if (!($user = User::current())) {
			return false;
		}
		if (isA($user, Has_Groups::class)) {
			$uri = View::link($callable[0], $callable[1]);
			return $this->checkFeatures($uri);
		}
		return true;
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

	//-------------------------------------------------------------------- overridePropertyDocComment
	/**
	 * Add object class overridden annotations at the beginning of the doc-comment
	 *
	 * @param $result string The doc-comment : we will prepend the access-overridden annotations here
	 * @param $object Reflection_Property
	 * @return string The doc-comment with access control override options
	 */
	public function overridePropertyDocComment($result, Reflection_Property $object)
	{
		static $anti_loop;
		if (empty($anti_loop)) {
			$anti_loop = true;
			$user = User::current();
			if (isA($user, Has_Groups::class)) {
				/** @var $user User|Has_Groups */
				$class_name = $object->getDeclaringClassName();
				$path = SL . str_replace(BS, SL, $class_name) . SL . Feature::OVERRIDE;
				if (
					$user
					&& !empty($feature = $user->getAccessOptions($path))
					&& isset($feature[$object->name])
				) {
					$annotations = [];
					foreach ($feature[$object->name] as $annotation_name => $annotation_value) {
						$annotations[] = '* @' . $annotation_name . SP . $annotation_value;
					}
					$result = join(LF, $annotations) . LF . $result;
				}
			}
			$anti_loop = false;
		}
		return $result;
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

		$aop->afterMethod ([Menu::class, 'constructItem'],  [$this, 'checkAccessToMenuItem']);
		$aop->afterMethod(
			[Reflection_Property::class, 'getOverrideDocComment'], [$this, 'overridePropertyDocComment']
		);
		$aop->afterMethod([View::class, 'link'], [$this, 'checkAccessToLink']);

		// TODO HIGH Lower security (see #100520#note-25)
		$aop->beforeMethod([Main::class, 'doRunInnerController'], [$this, 'checkAccess']);
		$aop->beforeMethod([Menu::class, 'constructBlock'], [$this, 'menuCheckAccess']);
		$aop->beforeMethod([View::class, 'run'], [$this, 'removeButtonsWithNoLink']);
	}

	//----------------------------------------------------------------------- removeButtonsWithNoLink
	/**
	 * Remove buttons which link is empty (eg due to access control limitation)
	 *
	 * @param $parameters Button[]
	 */
	public function removeButtonsWithNoLink(array &$parameters)
	{
		foreach (
			[Has_General_Buttons::GENERAL_BUTTONS, Has_Selection_Buttons::SELECTION_BUTTONS] as $buttons
		) {
			if (isset($parameters[$buttons])) {
				foreach ($parameters[$buttons] as $key => $button) {
					/** @var Button $button */
					if (empty($button->link)) {
						unset($parameters[$buttons][$key]);
					}
					else {
						foreach ($button->sub_buttons as $sub_key => $sub_button) {
							if (empty($sub_button->link)) {
								unset($button->sub_buttons[$sub_key]);
							}
						}
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
	 * @param $files   array[]
	 */
	private function setUri($new_uri, &$uri, array &$get, array &$post, array &$files)
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
