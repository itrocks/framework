<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;

/**
 * For class that want a customizable default object
 *
 * You may want to add @default Class_Name::getDefault to any property that want to use the default
 * May work with @user_default Class_Name::getDefault too
 *
 * TODO move @default from property to class annotation (default value for the @default property)
 */
trait Has_Default
{

	//-------------------------------------------------------------------------------------- $default
	/**
	 * @var boolean
	 */
	public $default = false;

	//------------------------------------------------------------------------------------ getDefault
	/**
	 * @return static|null
	 */
	public static function getDefault()
	{
		return Dao::searchOne(['default' => true], static::class);
	}

}
