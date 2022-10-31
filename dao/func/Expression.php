<?php
namespace ITRocks\Framework\Dao\Func;

use ITRocks\Framework\Sql\Builder\With_Build_Column;

/**
 * An expression that calls a function : property-path + function(s) assembly
 */
class Expression
{

	//------------------------------------------------------------------------------------- $function
	/**
	 * @var Column
	 */
	public Column $function;

	//--------------------------------------------------------------------------------------- $prefix
	/**
	 * Can be set on build to get a prefix for $property_path
	 *
	 * @var string
	 */
	public string $prefix;

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * @var string
	 */
	public string $property_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_path string|null
	 * @param $function      Column|null
	 */
	public function __construct(string $property_path = null, Column $function = null)
	{
		if (isset($function))      $this->function = $function;
		if (isset($property_path)) $this->property_path = $property_path;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * The value of the object as string is "$prefix$property_path"
	 *
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->prefix . $this->property_path;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @param $builder With_Build_Column
	 * @return string
	 */
	public function toSql(With_Build_Column $builder) : string
	{
		return $this->function->toSql($builder, $this->property_path);
	}

}
