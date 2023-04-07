<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Default_;
use ITRocks\Framework\Tools\Names;

/**
 * For class that want a customizable default object
 *
 * You may want to add #Default Class_Name::getDefault to any property that want to use the default
 * May work with @user_default Class_Name::getDefault too
 *
 * TODO move #Default from property to class annotation (default value for the #Default property)
 * TODO move @user_default from property to class annotation
 *
 * @after_write onlyOneDefault
 * @validate doNotRemoveDefault
 */
#[Default_('getDefault')]
trait Has_Default
{

	//-------------------------------------------------------------------------------------- $default
	public bool $default = false;

	//---------------------------------------------------------------------------- doNotRemoveDefault
	/** @noinspection PhpUnused @validate */
	public function doNotRemoveDefault() : bool|string
	{
		if ($this->default || !Dao::getObjectIdentifier($this)) {
			return true;
		}
		$before = Dao::read($this);
		if (!$before->default) {
			return true;
		}
		foreach (Dao::search(['default' => true], get_class($this)) as $object) {
			if (!Dao::is($object, $this)) {
				return true;
			}
		}
		$name = Loc::tr(Names::classToDisplay(get_class($this)));
		return Loc::tr('To change the default :name', Loc::replace(['name' => $name])) . ', '
			. Loc::tr('please set another one as default');
	}
	
	//------------------------------------------------------------------------------------ getDefault
	/** @return_constant */
	public static function getDefault() : ?static
	{
		return Dao::searchOne(['default' => true], static::class);
	}

	//-------------------------------------------------------------------------------- onlyOneDefault
	/**
	 * Called at each write : if default turned to true, reset default to false for all other stored
	 * objects
	 *
	 * @noinspection PhpUnused @after_write
	 */
	public function onlyOneDefault() : void
	{
		if (!$this->default) {
			return;
		}
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
