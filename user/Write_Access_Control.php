<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Application;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Menu\Construct_Item;
use ITRocks\Framework\Component\Menu\Item;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * Write access control plugin
 */
class Write_Access_Control implements Registerable
{
	use Has_Get;

	//--------------------------------------------------------------------------------- READ_FEATURES
	const READ_FEATURES = Feature::READ;

	//-------------------------------------------------------------------------------------- blankUri
	/**
	 * @return string
	 */
	protected function blankUri()
	{
		return View::link(Application::class, Feature::F_BLANK);
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
		if (User::current()) {
			return;
		}
		$uri_object = new Uri(lParse($uri, '?'));
		if (!in_array($uri_object->feature_name, static::READ_FEATURES)) {
			$uri = $this->blankUri();
			$get = $post = $files = [];
			$get[Parameter::AS_WIDGET] = true;
		}
	}

	//----------------------------------------------------------------------------- checkAccessToLink
	/**
	 * @param $result string The link (result of View::link())
	 */
	public function checkAccessToLink(&$result)
	{
		if (User::current()) {
			return;
		}
		$uri_object = new Uri(lParse($result, '?'));
		if (!in_array($uri_object->feature_name, static::READ_FEATURES)) {
			$result = $this->blankUri();
		}
	}

	//------------------------------------------------------------------------- checkAccessToMenuItem
	/**
	 * @param $result Item
	 */
	public function checkAccessToMenuItem(Item &$result = null)
	{
		if (User::current() || !isset($result)) {
			return;
		}
		$uri_object = new Uri(lParse($result->link, '?'));
		if (!in_array($uri_object->feature_name, static::READ_FEATURES)) {
			$result = null;
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([List_\Controller::class, 'getGeneralButtons'], [$this, 'removeButtons']);
		$aop->afterMethod([List_\Controller::class, 'getSelectionButtons'], [$this, 'removeButtons']);
		$aop->afterMethod([Construct_Item::class, 'constructItem'], [$this, 'checkAccessToMenuItem']);
		$aop->afterMethod([Output\Controller::class, 'getGeneralButtons'], [$this, 'removeButtons']);
		$aop->afterMethod([View::class, 'link'], [$this, 'checkAccessToLink']);
		$aop->beforeMethod([Main::class, 'doRunInnerController'], [$this, 'checkAccess']);
	}

	//--------------------------------------------------------------------------------- removeButtons
	/**
	 * @param $result Button[]
	 */
	public function removeButtons(array &$result)
	{
		if (User::current()) {
			return;
		}
		$buttons =& $result;
		foreach ($buttons as $button_key => $button) {
			if (!in_array($button->feature, static::READ_FEATURES)) {
				unset($buttons[$button_key]);
			}
		}
	}

}
