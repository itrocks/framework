<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Feature\List_\Summary_Builder;
use ITRocks\Framework\Sql\Builder;

/**
 * Used to retrieve property for use in function
 */
class Property implements Where
{
	use Has_To_String;

	//--------------------------------------------------------------------------------------- $prefix
	/**
	 * Column name prefix
	 *
	 * @var string
	 */
	public $prefix;

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The property path
	 *
	 * @var string
	 */
	public $property_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Property constructor
	 *
	 * @param $property_path string
	 * @param $prefix        string
	 */
	public function __construct($property_path, $prefix = '')
	{
		$this->property_path = $property_path;
		$this->prefix        = $prefix;
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
		return $builder->buildColumn($this->property_path, $this->prefix);
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * Returns the Dao function as SQL
	 *
	 * @param $builder       Builder\Where the sql query builder
	 * @param $property_path string the property path UNUSED
	 * @param $prefix        string column name prefix UNUSED
	 * @return string
	 */
	public function toSql(Builder\Where $builder, $property_path, $prefix = '')
	{
		return $builder->buildWhereColumn($this->property_path, $this->prefix);
	}

}
