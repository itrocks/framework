<?php
namespace SAF\Framework;

abstract class Acls implements Plugin
{

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add a new Acl to current user's group and to current connection's Acls
	 *
	 * To write result rights using Dao, call Dao::write(Acls_User::current()->group) after add.
	 * If you do not write them, modified Acls will keep active for current session only.
	 *
	 * @param $key string
	 * @param $value mixed     default is true
	 * @param $group Acl_Group default is current user group
	 */
	public static function add($key, $value = null, $group = null)
	{
		if (!isset($group)) {
			$group = Acls_User::current()->group;
		}
		if (!isset($value)) {
			$value = true;
		}
		$right = new Acl_Right();
		$right->group = $group;
		$right->key   = $key;
		$right->value = $value;
		self::current()->add($right);
		$group->rights[] = $right;
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
	 * To write result rights using Dao, call Dao::write(Acls_User::current()->group) after remove
	 * If you do not write them, modified Acls will keep active for current session only.
	 *
	 * @param $key string
	 * @param $group Acl_Group default is current use group
	 */
	public static function remove($key, $group = null)
	{
		if (!isset($group)) {
			$group = Acls_User::current()->group;
			self::current()->remove($key);
			foreach ($group->rights as $key => $right) {
				if ($right->key == $key) {
					unset($group->rights[$key]);
				}
			}
		}
	}

}
