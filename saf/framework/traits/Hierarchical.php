<?php
namespace SAF\Framework\Traits;

use SAF\Framework\Dao;
use SAF\Framework\Reflection\Link_Class;

/**
 * A trait for simple hierarchical business objects.
 *
 * Declare those complicated things into your class/trait :
 * - class/trait annotation : @after_write writeClassNames
 * - a property linked to its unique parent of the same class, named $super_class_name
 *   annotations : @link Object @var Class_Name @forein sub_class_names
 * - a property linked to all its children of the same class, named $sub_class_names
 *   annotations : @getter getSubClassNames @var Class_Name[] @foreign super_class_name
 *
 * TODO LOW add a single annotation on properties that will result in auto-calling of getSub
 * and writeSub without having to implement the getter and after_write into the business class.
 *
 * @business
 */
trait Hierarchical
{

	//--------------------------------------------------------------------------------------- readSub
	/**
	 * To use this :
	 * - Create your own getSubClassNames() method
	 * - Your method has no parameters
	 * - Your method must return Class_Name[]
	 * - Call return readSub('sub_class_names', 'super_class_name') using your two properties names
	 *
	 * @param $sub   string sub property name ie 'sub_class_names'
	 * @param $super string super property name ie 'super_class_name'
	 * @return object[]|Hierarchical[]
	 */
	protected function readSub($sub, $super)
	{
		if (!isset($this->$sub)) {
			$this->$sub = Dao::search([$super => $this], Link_Class::linkedClassNameOf($this));
		}
		return $this->$sub;
	}

	//-------------------------------------------------------------------------------------- writeSub
	/**
	 * To use this :
	 * - Create your own writeSubClassNames() method
	 * - Your method has no parameters
	 * - Your method returns nothing
	 * - Call return writeSub('sub_class_names', 'super_class_name') using your two properties names
	 *
	 * @param $sub   string sub property name ie 'sub_class_names'
	 * @param $super string super property name ie 'super_class_name'
	 */
	protected function writeSub($sub, $super)
	{
		$written = [];
		// update $super_property into new $sub_properties
		foreach ($this->$sub as $sub) {
			if (!Dao::is($this, $sub->$super)) {
				$sub->$super = $this;
				Dao::write($sub, [Dao::only($super)]);
			}
			$written[Dao::getObjectIdentifier($sub)] = true;
		}
		// empty $super_property from removed $sub_properties
		$subs = Dao::search([$super => $this], Link_Class::linkedClassNameOf($this));
		foreach ($subs as $sub) {
			if (!isset($written[Dao::getObjectIdentifier($sub)])) {
				$sub->$super = null;
				Dao::write($sub, [Dao::only($super)]);
			}
		}
	}

}
