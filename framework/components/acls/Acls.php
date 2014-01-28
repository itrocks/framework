<?php
namespace SAF\Framework;

/**
 * Main acls plugin
 */
abstract class Acls implements Plugin
{

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
	/**
	 * @param $key string
	 * @return string|array
	 */
	public static function get($key)
	{
		return self::current()->get($key);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::addAfterMethodCall(
			array('SAF\Framework\User_Authentication', "authenticate"),
			array('SAF\Framework\Acls_Loader', "onUserAuthenticate")
		);
		Aop::addAfterMethodCall(
			array('SAF\Framework\User_Authentication', "disconnect"),
			array('SAF\Framework\Acls_Loader', "onUserDisconnect")
		);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an Acls to current group and from current connection's Acls
	 *
	 * @param $object Acls_Right|string
	 * @param $group  Acls_Group default is current use group
	 * @param $save   boolean if true, Modifier acls group is saved
	 */
	public static function remove($object, $group = null, $save = false)
	{
		if (!isset($group)) {
			$group = Acls_User::current()->group;
		}
		$key = ($object instanceof Acls_Right) ? $object : $object;
		self::current()->remove($key);
		$group->remove($key);
		if ($save) {
			Dao::write($group);
		};
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Add a new Acl to current user's group and to current connection's Acls
	 *
	 * @param $object Acls_Right|string
	 * @param $value  string     default is string value of true
	 * @param $group  Acls_Group default is current user group
	 * @param $save   boolean    if true, Modifier acls group is saved
	 */
	public static function set($object, $value = null, $group = null, $save = false)
	{
		if (!isset($group)) {
			$group = Acls_User::current()->group;
		}
		if (!isset($value) && !($object instanceof Acls_Right)) {
			$value = true;
		}
		self::current()->add($object, $value);
		$group->add($object, $value);
		if ($save) {
			Dao::write($group);
		};
	}

}
