<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;
use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * An object representation of a mysql index key
 */
class Key implements Sql\Key
{

	//---------------------------------------------------------------------------------- $Cardinality
	private int $Cardinality;

	//------------------------------------------------------------------------------------ $Collation
	private string $Collation;

	//---------------------------------------------------------------------------------- $Column_name
	private string $Column_name;

	//-------------------------------------------------------------------------------------- $Comment
	private string $Comment;

	//-------------------------------------------------------------------------------- $Index_comment
	private string $Index_comment = '';

	//----------------------------------------------------------------------------------- $Index_type
	#[Values('BTREE, FULLTEXT, SPATIAL, UNIQUE,')]
	private string $Index_type = '';

	//------------------------------------------------------------------------------------- $Key_name
	private string $Key_name;

	//----------------------------------------------------------------------------------- $Non_unique
	private bool $Non_unique;

	//----------------------------------------------------------------------------------------- $Null
	private bool $Null;

	//--------------------------------------------------------------------------------------- $Packed
	private bool $Packed;

	//--------------------------------------------------------------------------------- $Seq_in_index
	private int $Seq_in_index;

	//------------------------------------------------------------------------------------- $Sub_part
	private mixed $Sub_part;

	//---------------------------------------------------------------------------------------- $Table
	private string $Table;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string|null
	 */
	public function __construct(string $name = null)
	{
		if (isset($name)) {
			$this->Column_name = $this->Key_name = $name;
		}
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * TODO to be tested
	 *
	 * @param $key Sql\Key
	 * @return boolean
	 */
	public function equiv(Sql\Key $key) : bool
	{
		return $key->Column_name === $this->Column_name;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->Key_name;
	}

	//------------------------------------------------------------------------------ getSqlColumnName
	/**
	 * @return string
	 */
	public function getSqlColumnName() : string
	{
		return $this->Column_name;
	}

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * @return string
	 */
	public function getSqlType() : string
	{
		return $this->Index_type;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return string
	 */
	public function getType() : string
	{
		return $this->Index_type;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql() : string
	{
		return BQ . $this->Column_name . BQ;
	}

}
