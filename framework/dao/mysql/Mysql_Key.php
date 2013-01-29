<?php
namespace SAF\Framework;

class Mysql_Key implements Dao_Key
{

	//---------------------------------------------------------------------------------- $Cardinality
	/**
	 * @var integer
	 */
	//private $Cardinality;

	//-------------------------------------------------------------------------------------- $Comment
	/**
	 * @var string
	 */
	//private $Comment;

	//------------------------------------------------------------------------------------ $Collation
	/**
	 * @var string
	 */
	//private $Collation;

	//---------------------------------------------------------------------------------- $Column_name
	/**
	 * @var string
	 */
	private $Column_name;

	//-------------------------------------------------------------------------------- $Index_comment
	/**
	 * @var string
	 */
	//private $Index_comment;

	//----------------------------------------------------------------------------------- $Index_type
	/**
	 * @var string
	 * @values UNIQUE, FULLTEXT, SPATIAL
	 */
	private $Index_type;

	//------------------------------------------------------------------------------------- $Key_name
	/**
	 * @var string
	 */
	private $Key_name;

	//----------------------------------------------------------------------------------- $Non_unique
	/**
	 * @var boolean
	 */
	//private $Non_unique;

	//----------------------------------------------------------------------------------------- $Null
	/**
	 * @var boolean
	 */
	//private $Null;

	//--------------------------------------------------------------------------------------- $Packed
	/**
	 * @var boolean
	 */
	//private $Packed;

	//--------------------------------------------------------------------------------- $Seq_in_index
	/**
	 * @var integer
	 */
	//private $Seq_in_index;

	//------------------------------------------------------------------------------------- $Sub_part
	/**
	 * @var mixed
	 */
	//private $Sub_part;

	//---------------------------------------------------------------------------------------- $Table
	/**
	 * @var string
	 */
	//private $Table;

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * @todo to be tested
	 *
	 * @param Mysql_Key $key
	 * @return boolean
	 */
	public function equiv($key)
	{
		return $key->Column_name === $this->Column_name;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->Key_name;
	}

	//------------------------------------------------------------------------------ getSqlColumnName
	/**
	 * @return string
	 */
	public function getSqlColumnName()
	{
		return $this->Column_name;
	}

	//------------------------------------------------------------------------------------ getSqlType
	/**
	 * @return string
	 */
	public function getSqlType()
	{
		return $this->Index_type;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->Index_type;
	}

	//----------------------------------------------------------------------------------------- toSql
	/**
	 * @return string
	 */
	public function toSql()
	{
		return "`" . $this->Column_name . "`";
	}

}
