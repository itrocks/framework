<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Map;
use ITRocks\Framework\Reflection\Link_Class;

/**
 * A trait for simple hierarchical business objects.
 *
 * Declare those complicated things into your class/trait :
 *
 * for sub-objects :
 * - implement your own protected function writeSubClassNames containing $this->writeSub();
 * - class/trait annotation : @after_write writeSubClassNames
 * - a property linked to its unique parent of the same class, named $super_class_name
 *   annotations : @link Object @var Class_Name @forein sub_class_names
 *
 * for super-object :
 * - implement your own protected function getSubClassNames containing $this->readSub();
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

	//------------------------------------------------------------------------------------- getAllSub
	/**
	 * Gets all sub objects from all sub property objects (recursively)
	 *
	 * To use this :
	 * - Create your own getAllSubClassNames() method
	 * - Call return getAllSub()
	 *
	 * Please do not write a hierarchy you read with $limits ! You would lose data.
	 *
	 * @param $sub_property    string  sub objects property name
	 * @param $super_property  string  super object property name
	 * @param $limit_recursion integer limit objects recursion depth to avoid cyclic recursion
	 * @param $limit_objects   integer limit number of results to avoid long searches
	 * @return static[]
	 */
	protected function getAllSub(
		string $sub_property, string $super_property, int $limit_recursion = PHP_INT_MAX,
		int $limit_objects = PHP_INT_MAX
	) : array
	{
		if ($limit_recursion <= 0) {
			return [];
		}
		$limit_recursion --;
		$objects          = $this->readSub($sub_property, $super_property, $limit_objects);
		$limit_objects   -= count($objects);
		$map              = new Map($objects);
		foreach ($objects as $sub) {
			if ($limit_objects <= 0) {
				break;
			}
			$sub_objects = $sub->getAllSub(
				$sub_property, $super_property, $limit_recursion, $limit_objects
			);
			$limit_objects -= count($sub_objects);
			$map->add($sub_objects);
		}
		return $objects;
	}

	//----------------------------------------------------------------------------------- getAllSuper
	/**
	 * Gets all parent objects from the super property (recursively)
	 *
	 * The resulting list will begin with the top object, then descends until the super-object
	 *
	 * To use this :
	 * - Create your own getAllSuperClassNames() method
	 * - Call return getAllSuper()
	 *
	 * @param $super_property string super object property name
	 * @param $limit          integer limit objects recursion depth to avoid cyclic recursion
	 * @return static[]
	 */
	protected function getAllSuper(string $super_property, int $limit = 100) : array
	{
		if (!$limit) {
			return [];
		}
		$objects = [];
		$super   = $this->getSuper($super_property);
		$map     = new Map($objects);
		while ($super && $limit--) {
			$map->add($super);
			$super = $super->getSuper($super_property);
		}
		return array_reverse($objects, true);
	}

	//-------------------------------------------------------------------------------------- getSuper
	/**
	 * Gets super object
	 *
	 * @param $super_property string super object property name
	 * @return ?static
	 */
	protected function getSuper(string $super_property) : ?static
	{
		return $this->$super_property;
	}

	//---------------------------------------------------------------------------------------- getTop
	/**
	 * Gets top object
	 *
	 * @param $super_property string super object property name
	 * @return static
	 */
	protected function getTop(string $super_property) : static
	{
		$super = $this->getSuper($super_property);
		return ($super ? $super->getTop($super_property) : $this);
	}

	//--------------------------------------------------------------------------------------- readSub
	/**
	 * To use this :
	 * - Create your own readSubClassNames() method
	 * - Call return readSub('sub_class_names', 'super_class_name') using your two property names
	 *
	 * Please do not write a hierarchy you read with a $limit ! You would lose data.
	 *
	 * @param $sub_property   string sub objects property name
	 * @param $super_property string super object property name
	 * @param $limit          integer
	 * @return static[]
	 */
	protected function readSub(string $sub_property, string $super_property, int $limit = PHP_INT_MAX)
		: array
	{
		if (isset($this->$sub_property)) {
			return $this->$sub_property;
		}
		$this->$sub_property = Dao::getObjectIdentifier($this)
			? Dao::search(
				[$super_property => $this], Link_Class::linkedClassNameOf($this), Dao::limit($limit)
			)
			: [];
		return $this->$sub_property;
	}

	//-------------------------------------------------------------------------------------- writeSub
	/**
	 * To use this :
	 * - Create your own writeSubClassNames() method
	 * - Your method has no parameters
	 * - Your method returns nothing
	 * - Call return writeSub('sub_class_names', 'super_class_name') using your two property names
	 *
	 * @param $sub_property   string sub objects property name
	 * @param $super_property string super object property name
	 */
	protected function writeSub(string $sub_property, string $super_property) : void
	{
		$written = [];
		// update $super_property into new $sub_properties
		foreach ($this->$sub_property as $sub) {
			if (!Dao::is($this, $sub->$super_property)) {
				$sub->$super_property = $this;
				Dao::write($sub, Dao::only($super_property));
			}
			$written[Dao::getObjectIdentifier($sub)] = true;
		}

		// empty $super_property from removed $sub_properties
		$subs = Dao::search([$super_property => $this], Link_Class::linkedClassNameOf($this));
		foreach ($subs as $sub) {
			if (!isset($written[Dao::getObjectIdentifier($sub)])) {
				$sub->$super_property = null;
				Dao::write($sub, Dao::only($super_property));
			}
		}
	}

}
