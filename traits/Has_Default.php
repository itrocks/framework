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
 * TODO move @user_default from property to class annotation
 *
 * @after_write onlyOneDefault
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
	 * @return_constant
	 */
	public static function getDefault()
	{
		return Dao::searchOne(['default' => true], static::class);
	}

	//-------------------------------------------------------------------------------- onlyOneDefault
	/**
	 * Called at each write : if default turned to true, reset default to false for all other stored
	 * objects
	 */
	public function onlyOneDefault()
	{
		if ($this->default) {
			Dao::begin();
			foreach (Dao::search(['default' => true], static::class) as $object) {
				if (!Dao::is($object, $this)) {
					$object->default = false;
					Dao::write($object, Dao::only('default'));
				}
			}
			Dao::commit();
		}
	}

}
