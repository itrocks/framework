<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\AOP\Joinpoint\Method_Joinpoint;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\User;
use ITRocks\Framework\Widget\Add;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Edit;
use ITRocks\Framework\Widget\List_;
use ITRocks\Framework\Widget\Menu;
use ITRocks\Framework\Widget\Menu\Item;
use ITRocks\Framework\Widget\Output;
use ITRocks\Framework\Widget\Write;

/**
 * Write access control plugin
 */
class Write_Access_Control implements Registerable
{

	//-------------------------------------------------------------------------------- WRITE_FEATURES
	const WRITE_FEATURES = [
		Feature::F_ADD,
		Feature::F_API,
		Feature::F_DELETE,
		Feature::F_DUPLICATE,
		Feature::F_EDIT,
		Feature::F_IMPORT,
		Feature::F_TRANSFORM,
		Feature::F_VALIDATE,
		Feature::F_WRITE
	];

	//--------------------------------------------------------------------------------- accessControl
	/**
	 * @param $joinpoint Method_Joinpoint
	 */
	public function accessControl(Method_Joinpoint $joinpoint)
	{
		if (!User::current()) {
			$joinpoint->stop = true;
		}
	}

	//------------------------------------------------------------------------- checkAccessToMenuItem
	/**
	 * @param $result Item
	 */
	public function checkAccessToMenuItem(Item &$result)
	{
		if (isset($result)) {
			$user = User::current();
			if (!$user) {
				$uri = new Uri($result->link);
				if (in_array($uri->feature_name, self::WRITE_FEATURES)) {
					$result = null;
				}
			}
		}
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
		$aop->beforeMethod(
			[Add\Controller::class, 'run'],                  [$this, 'accessControl']
		);
		$aop->beforeMethod(
			[Edit\Controller::class, 'run'],                 [$this, 'accessControl']
		);
		$aop->afterMethod(
			[List_\Controller::class, 'getGeneralButtons'],  [$this, 'removeButtons']
		);
		$aop->afterMethod(
			[Menu::class, 'constructItem'],                  [$this, 'checkAccessToMenuItem']
		);
		$aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'], [$this, 'removeButtons']
		);
		$aop->beforeMethod(
			[Write\Controller::class, 'run'],                [$this, 'accessControl']
		);
	}

	//--------------------------------------------------------------------------------- removeButtons
	/**
	 * @param $result Button[]
	 */
	public function removeButtons(array &$result)
	{
		if (!User::current()) {
			foreach (self::WRITE_FEATURES as $feature) {
				if (isset($result[$feature])) {
					unset($result[$feature]);
				}
			}
		}
	}

}
