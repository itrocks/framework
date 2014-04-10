<?php
namespace SAF\Framework\Checker;

use SAF\Framework\Checker\Report\Line;

/**
 * A check report stores business objects checking results into Check_Report_Lines objects
 */
class Report
{

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var Line[]
	 */
	public $lines = [];

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $check_report_lines Line[]
	 */
	public function add($check_report_lines)
	{
		$this->lines = array_merge($this->lines, $check_report_lines);
	}

}
