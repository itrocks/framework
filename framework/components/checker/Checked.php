<?php
namespace SAF\Framework;

/**
 * This is an interface for auto-checked business objects
 */
interface Checked
{

	//----------------------------------------------------------------------------------------- check
	/**
	 * Check current business object and returns check report
	 *
	 * @return Check_Report
	 */
	public function check();

}
