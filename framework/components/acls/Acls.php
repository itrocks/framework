<?php
namespace SAF\Framework;

abstract class Acls implements Plugin
{

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add a new Acl to current user's group and to current connection's Acls
	 *
	 * @param $key   string
	 * @param $value mixed     default is true
	 * @param $group Acl_Group default is current user group
	 * @param $save  boolean   if true, Modifier acls group is saved
	 */
	public static function add($key, $value = null, $group = null, $save = false)
	{
		if (!isset($group)) {
			$group = Acls_User::current()->group;
		}
		if (!isset($value)) {
			$value = true;
		}
		$right = new Acl_Right($group, $key, $value);
		$group->rights[] = $right;
		if ($save) {
			Dao::write($group);
		};
		self::current()->add($right);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Acls_Rights
	 * @return Acls_Rights
	 */
	public static function current(Acls_Rights $set_current = null)
	{
		return Acls_Rights::current($set_current);
	}

	//------------------------------------------------------------------------------------------- get
	public static function get($key)
	{
		return self::current()->get($key);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			__NAMESPACE__ . "\\User_Authenticate_Controller->authenticate()",
			array(__NAMESPACE__ . "\\Acls_Loader", "onUserAuthenticate")
		);
		Aop::add("after",
			__NAMESPACE__ . "\\User_Authenticate_Controller->disconnect()",
			array(__NAMESPACE__ . "\\Acls_Loader", "onUserDisconnect")
		);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an Acls to current group and from current connection's Acls
	 *
	 * @param $key   string
	 * @param $group Acl_Group default is current use group
	 * @param $save  boolean   if true, Modifier acls group is saved
	 */
	public static function remove($key, $group = null, $save = false)
	{
		self::current()->remove($key);
		if (!isset($group)) {
			$group = Acls_User::current()->group;
		}
		$removed = false;
		foreach ($group->rights as $k => $right) {
			if ($right->key == $key) {
				unset($group->rights[$k]);
				$removed = true;
			}
		}
		if ($save && $removed) {
			Dao::write($group);
		};
	}

}
