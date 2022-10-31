<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;

/**
 * An object representation of a mysql index key
 */
class Key implements Sql\Key
{

	//---------------------------------------------------------------------------------- $Cardinality
	/**
	 * @var integer
	 */
	private int $Cardinality;

	//-------------------------------------------------------------------------------------- $Comment
	/**
	 * @var string
	 */
	private string $Comment;

	//------------------------------------------------------------------------------------ $Collation
	/**
	 * @var string
	 */
	private string $Collation;

	//---------------------------------------------------------------------------------- $Column_name
	/**
	 * @var string
	 */
	private string $Column_name;

	//-------------------------------------------------------------------------------- $Index_comment
	/**
	 * @var string
	 */
	private string $Index_comment;

	//----------------------------------------------------------------------------------- $Index_type
	/**
	 * @values BTREE, FULLTEXT, SPATIAL, UNIQUE,
	 * @var string
	 */
	private string $Index_type;

	//------------------------------------------------------------------------------------- $Key_name
	/**
	 * @var string
	 */
	private string $Key_name;

	//----------------------------------------------------------------------------------- $Non_unique
	/**
	 * @var boolean
	 */
	private bool $Non_unique;

	//----------------------------------------------------------------------------------------- $Null
	/**
	 * @var boolean
	 */
	private bool $Null;

	//--------------------------------------------------------------------------------------- $Packed
	/**
	 * @var boolean
	 */
	private bool $Packed;

	//--------------------------------------------------------------------------------- $Seq_in_index
	/**
	 * @var integer
	 */
	private int $Seq_in_index;

	//------------------------------------------------------------------------------------- $Sub_part
	/**
	 * @var mixed
	 */
	private mixed $Sub_part;

	//---------------------------------------------------------------------------------------- $Table
	/**
	 * @var string
	 */
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
