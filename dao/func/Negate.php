<?php
namespace SAF\Framework\Dao\Func;

/**
 * A Dao negate function applies only to function that have a negative : it negates the function
 */
interface Negate extends Dao_Function
{

	//---------------------------------------------------------------------------------------- negate
	/**
	 * Negate the Dao function
	 */
	public function negate();

}
