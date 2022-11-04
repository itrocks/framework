<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Component\Menu;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Group;

/**
 * Features user access control
 *
 * Must be enabled if you enable a menu for administrators to configure user groups
 */
class Admin_Plugin implements Installable, Registerable, Updatable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return 'Features user access control';
	}

	//-------------------------------------------------------------------- initializeDefaultUserGroup
	/**
	 * Called on feature install
	 *
	 * All users set as super-administrator, if they have no group
	 */
	public function initializeDefaultUserGroup() : void
	{
		// create the default super-administrator group
		$group_name = Loc::tr('Super-administrator');
		$group      = Dao::searchOne(['name' => $group_name], Group::class) ?: new Group();
		if (!$group->features && !$group->name) {
			$group->name     = $group_name;
			$group->features = [
				Dao::searchOne(['path' => 'ITRocks/Framework/User/superAdministrator'], Feature::class)
			];
			Dao::write($group);
		}
		// assign all unassigned users to super-administrator
		foreach (Dao::search(['groups' => Func::isNull()], Groups_User::class) as $user) {
			$user->groups = [$group];
			Dao::write($user, Dao::only('groups'));
		}
		// reset current user features access list
		User\Authenticate\Authentication::authenticate(User::current());
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @param $installer Installer
	 */
	public function install(Installer $installer) : void
	{
		$installer->addPlugin($this);
		$installer->addToClass(User::class, Has_Groups::class);
		$installer->addMenu(['Administration' => Menu::configurationOf(Group::class)]);
		$this->update(0);
		$this->initializeDefaultUserGroup();
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		Application_Updater::get()->addUpdatable($this);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * The full feature cache is update each time the application is updated
	 *
	 * @param $last_time integer
	 */
	public function update(int $last_time) : void
	{
		$feature_cache = new Feature_Cache();
		if ($files = $feature_cache->invalidate($last_time)) {
			$feature_cache->saveToCache($feature_cache->scanFeatures($files));
		}
	}

}
