<?php
namespace ITRocks\Framework\Dao\Mysql\Information_Schema;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
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
	protected Link $link;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link Link|null
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
	 * @param $property_name string|null If set, check only locks from this property
	 * @param $rule          string @values Rule::const
	 * @return Lock_Objects[]
	 */
	public function whoIsLockedBy(
		object $object, string $property_name = null, string $rule = Rule::DELETE
	) : array
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
	public function whoLocks(object $object, string $rule = Rule::DELETE) : array
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
			if (!$dependency) {
				continue;
			}
			$class_name = $dependency->class_name;
			// TODO Move this search into a common method to get the property name from a column name
			$property_name = $constraint->column_name;
			if (str_starts_with($property_name, 'id_')) {
				$property_name = substr($property_name, 3);
			}
			/** @noinspection PhpUnhandledExceptionInspection */
			foreach ((new Reflection_Class($class_name))->getProperties() as $property) {
				if (Alias::of($property)->value === $property_name) {
					$property_name = $property->name;
				}
				if (Store_Name_Annotation::of($property)->value === $property_name) {
					$property_name = $property->name;
					break;
				}
			}
			$count = $this->link->count([$property_name => $identifier], $class_name);
			if (!$count) {
				continue;
			}
			$objects = $this->link->search([$property_name => $identifier], $class_name, Dao::limit(2));
			$lock_objects[] = new Lock_Objects($object, $class_name, $property_name, $objects, $count);
		}
		return $lock_objects;
	}

}
