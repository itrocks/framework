<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Func\Concat;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Sql;
use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Tools\Date_Time;

/**
 * For sql builders that need a buildColumn() function
 *
 * They will automatically get Has_Joins, so they do not need to use Has_Joins.
 */
#[Extend(With_Build_Column::class)]
trait Has_Build_Column
{
	use Has_Joins;

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $path            string The path of the property
	 * @param $as              boolean If false, prevent 'AS' clause to be added
	 * @param $resolve_objects boolean If true, a property path for an object will be replaced with a
	 *                         CONCAT of its representative values
	 * @param $join            Join|null For optimisation purpose, if join is already known
	 * @return string
	 */
	public function buildColumn(
		string $path, bool $as = true, bool $resolve_objects = false, Join $join = null
	) : string
	{
		if (str_contains($path, BQ)) {
			// already built (called twice on Expression)
			return $path;
		}
		if (!$join) {
			$join = $this->joins->add($path);
		}
		[$master_path, $column_name] = Sql\Builder::splitPropertyPath($path);
		if (!$join) {
			$join = $this->joins->getJoin($master_path);
		}
		if (
			$resolve_objects
			&& ($class_name = $this->joins->getClass($path))
			&& !is_a($class_name, Date_Time::class, true)
		) {
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			$class             = new Reflection_Class($class_name);
			$concat_properties = [];
			foreach (Representative::of($class)->values as $property_name) {
				$concat_properties[] = $path . DOT . $property_name;
			}
			$concat = new Concat($concat_properties);
			/** @var $this With_Build_Column|self */
			$sql = $concat->toSql(
				$this, ($as && ($this instanceof Columns) && $this->resolve_aliases) ? $path : ''
			);
		}
		else {
			$force_column = (
				($property = $this->joins->getProperty($master_path, $column_name))
				&& Store::of($property)->isFalse()
			) ? 'NULL' : null;
			if (($path === DOT) && !$column_name) {
				$as   = false;
				$path = '*';
			}
			$sql = $force_column ?: (
				$join
					? ($join->foreign_alias . DOT . BQ . $column_name . BQ)
					: ($this->joins->rootAlias() . DOT . (($path === '*') ? $path : (BQ . $path . BQ)))
			);
			if (isset($this->translate[$path])) {
				$path = $this->translate[$path];
				$sql  = 'IFNULL(NULLIF(' . $sql . ', ""), ' . $join->masterSql() . ')';
			}
			if (
				($column_name === $path)
				&& !isset($this->properties[$path])
				&& !in_array($column_name, ['id', 'representative'])
				&& ($alias = array_search($path, $this->properties))
				&& !is_numeric($alias)
			) {
				$path = $alias;
			}
			$sql
				.= ($as && ($column_name !== $path) && ($this instanceof Columns) && $this->resolve_aliases)
				? (' AS ' . BQ . $path . BQ)
				: '';
		}
		return $sql;
	}

}
