<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao\Sql;

/**
 * An object representation of a mysql index key
 */
class Key implements Sql\Key
{

	/**
	 * @var integer
	 */
	//private $Cardinality;

	/**
	 * @var string
	 */
	//private $Comment;

	/**
	 * @var string
	 */
	//private $Collation;

	//---------------------------------------------------------------------------------- $Column_name
	/**
	 * @var string
	 */
	private $Column_name;

	/**
	 * @var string
	 */
	//private $Index_comment;

	//----------------------------------------------------------------------------------- $Index_type
	/**
	 * @values BTREE, FULLTEXT, SPATIAL, UNIQUE,
	 * @var string
	 */
	private $Index_type;

	//------------------------------------------------------------------------------------- $Key_name
	/**
	 * @var string
	 */
	private $Key_name;

	/**
	 * @var boolean
	 */
	//private $Non_unique;

	/**
	 * @var boolean
	 */
	//private $Null;

	/**
	 * @var boolean
	 */
	//private $Packed;

	/**
	 * @var integer
	 */
	//private $Seq_in_index;

	/**
	 * @var mixed
	 */
	//private $Sub_part;

	/**
	 * @var string
	 */
	//private $Table;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) {
			$this->Column_name = $this->Key_name = $name;
		}
	}

	//----------------------------------------------------------------------------------------- equiv
	/**
	 * @todo to be tested
	 *
	 * @param Key $key
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
		return BQ . $this->Column_name . BQ;
	}

}
