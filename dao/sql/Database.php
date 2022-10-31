<?php
namespace ITRocks\Framework\Dao\Sql;

/**
 * A common interface for Dao database object representation
 */
interface Database
{

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	public function getName() : string;

}
