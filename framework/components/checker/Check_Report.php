<?php
namespace SAF\Framework;

/**
 * A check report stores business objects checking results into Check_Report_Lines objects
 */
class Check_Report
{

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @var Check_Report_Line[]
	 */
	public $lines = array();

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $check_report_lines Check_Report_Line|Check_Report_Line[]
	 */
	public function add($check_report_lines)
	{
		if (is_array($check_report_lines)) {
			$this->lines = array_merge($this->lines, $check_report_lines);
		}
		else {
			$this->lines[] = $check_report_lines;
		}
	}

}
