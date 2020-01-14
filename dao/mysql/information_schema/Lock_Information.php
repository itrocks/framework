<?php
namespace ITRocks\Framework\Dao\Mysql\Information_Schema;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Allow to get information about objects that lock others objects
 *
 * - deletion lock
 * - update lock
 * - answers "which objects does an object lock ?"
 * - answers "which objects are locking an object ?"
 */
class Lock_Information
{

	//------------------------------------------------------------------------------ CONSTRAINT_QUERY
	const CONSTRAINT_QUERY = <<<EOT
		SELECT k.*
		FROM       information_schema.key_column_usage k
		INNER JOIN information_schema.referential_constraints r
			ON  r.constraint_name   = k.constraint_name
			AND r.constraint_schema = k.constraint_schema
		WHERE k.constraint_schema     = 'database'
		AND k.referenced_table_name   = 'table'
		AND k.referenced_table_schema = 'database'
		AND r.delete_rule             = 'RESTRICT';
EOT;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var Link
	 */
	protected $link;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link Link
	 */
	public function __construct(Link $link = null)
	{
		$this->link = $link ?: Dao::current();
	}

	//--------------------------------------------------------------------------------- whoIsLockedBy
	/**
	 * Which objects does this object lock ?
	 *
	 * @param $object        object
	 * @param $property_name string If set, check only locks from this property
	 * @param $rule          string @values Rule::const
	 * @return Lock_Objects[]
	 */
	public function whoIsLockedBy($object, $property_name = null, $rule = Rule::DELETE)
	{
		// TODO
		return [];
	}

	//-------------------------------------------------------------------------------------- whoLocks
	/**
	 * Which objects are locking this object ?
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @param $rule   string @values Rule::const
	 * @return Lock_Objects[]
	 */
	public function whoLocks($object, $rule = Rule::DELETE)
	{
		$class_name   = get_class($object);
		$lock_objects = [];
		$query        = strReplace(
			[
				Q . 'database' . Q => Q . $this->link->getConnection()->database . Q,
				Q . 'table' . Q    => Q . $this->link->storeNameOf($class_name) . Q,
				'delete_rule'      => $rule . '_rule'
			],
			static::CONSTRAINT_QUERY
		);
		$identifier = $this->link->getObjectIdentifier($object);
		foreach ($this->link->query($query, Key_Column_Usage::class) as $constraint) {
			/** @var $constraint Key_Column_Usage */
			$dependency = Dao::searchOne(
				['dependency_name' => $constraint->table_name, 'type' => Dependency::T_STORE],
				Dependency::class
			);
			$class_name = $dependency->class_name;
			// TODO Move this search into a common method to get the property name from a column name
			$property_name = $constraint->column_name;
			if (substr($property_name, 0, 3) === 'id_') {
				$property_name = substr($property_name, 3);
			}
			/** @noinspection PhpUnhandledExceptionInspection */
			foreach ((new Reflection_Class($class_name))->getProperties() as $property) {
				if (Alias_Annotation::of($property)->value === $property_name) {
					$property_name = $property->name;
				}
				if (Store_Name_Annotation::of($property)->value === $property_name) {
					$property_name = $property->name;
					break;
				}
			}
			$objects = $this->link->search([$property_name => $identifier], $class_name);
			if ($objects) {
				$lock_objects = new Lock_Objects($object, $class_name, $property_name, $objects);
			}
		}
		return $lock_objects;
	}

}
