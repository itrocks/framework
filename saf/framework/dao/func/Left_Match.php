<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Sql\Builder;
use SAF\Framework\Sql\Value;

/**
 * Dao Left_Match function
 */
class Left_Match implements Where
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed|Where
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value mixed|Where
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path)
	{
		$column = $builder->buildColumn($property_path);
		$value  = Value::escape($this->value);
		return $column . ' = LEFT(' . $value . ', LENGTH(' . $column . '))';
	}

}
