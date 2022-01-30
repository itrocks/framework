<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * A DAO reverse option for use as a column name for Sort
 */
class Reverse implements Option
{

	//--------------------------------------------------------------------------------------- $column
	/**
	 * Column name for the reverse sort
	 *
	 * @var string
	 */
	public $column;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $column string
	 */
	public function __construct($column)
	{
		$this->column = $column;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->column . SP . 'reverse';
	}

}
