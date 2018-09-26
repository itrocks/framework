<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Widget\List_\Summary_Builder;

/**
 * Dao FIND_IN_SET function (mysql specific)
 */
class In_Set implements Negate, Where
{
	use Has_To_String;

	//------------------------------------------------------------------------------------------ $not
	/**
	 * If true, then this is a 'NOT FIND_IN_SET' instead of a 'FIND_IN_SET'
	 *
	 * @var boolean
	 */
	public $not;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed[]
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $not   boolean
	 */
	public function __construct($value = null, $not = false)
	{
		if (isset($value)) $this->value = $value;
		if (isset($not))   $this->not   = $not;
	}

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate()
	{
		$this->not = !$this->not;
	}

	//--------------------------------------------------------------------------------------- toHuman
	/**
	 * Returns the Dao function as Human readable string
	 *
	 * @param $builder       Summary_Builder the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toHuman(Summary_Builder $builder, $property_path, $prefix = '')
	{
		$summary = '';
		if ($this->value) {
			list($translation_delimiter) = $builder->getTranslationDelimiters();

			$summary = $translation_delimiter . sprintf(
				Loc::tr($this->not ? '%s does not contain %s' : '%s contains %s'),
				$builder->buildColumn($property_path, $prefix, $builder::SUB_TRANSLATE),
				$builder->buildScalar($this->value, $property_path, $builder::SUB_TRANSLATE)
			) . $translation_delimiter;
		}
		return $summary;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @param $prefix        string column name prefix
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		$sql = '';
		if ($this->value) {
			$sql = ($this->not ? ' NOT' : '') . ' FIND_IN_SET('
				. Value::escape($this->value, false, $builder->getProperty($property_path))
				. ', ' . $builder->buildWhereColumn($property_path, $prefix) . ')';
		}
		return $sql;
	}

}
