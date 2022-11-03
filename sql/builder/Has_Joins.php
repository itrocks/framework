<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Sql\Join\Joins;

/**
 * For builder that have joins
 */
trait Has_Joins
{

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * Sql joins
	 *
	 * @var Joins
	 */
	private Joins $joins;

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins() : Joins
	{
		return $this->joins;
	}

}
