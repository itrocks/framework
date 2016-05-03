<?php
namespace SAF\Framework\Widget\Data_List\Search_Parameters_Parser;

use SAF\Framework\Tools\Date_Time;
use SAF\Framework\Widget\Data_List\Search_Parameters_Parser;

/**
 * Tests that should pass date and date time column
 *
 * Note: These tests are greatly dependants of object structure managed by SAF\Framework\Dao !!!
 *
 *
 * 2016
 * 03/2016
 * 3/2016
 * 05/03/2016
 * 5/3/2016
 * 05/3/2016
 * 5/03/2016
 * 05 (day of current month/year)
 * 5 (day of current month/year)
 * 05/03 (day and month of current year)
 * 5/3 (day and month of current year)
 * 05/3 (day and month of current year)
 * 5/03 (day and month of current year)
 * vide
 * aucun
 * aucune
 * nul

 * 2015-2016
 * 2015-03/2016
 * 2015-3/2016
 * 2015-05/03/2016
 * 2015-5/3/2016
 * 2015-05/3/2016
 * 2015-5/03/2016
 * 2015-05
 * 2015-5
 * 2015-05/03
 * 2015-5/3
 * 2015-05/3
 * 2015-5/03
 *
 * 07/2015-2016
 * ...
 *
 * 7/2015-2016
 * ...
 *
 * 09/07/2015-2016
 * ...
 *
 * 9/7/2015-2016
 * ...
 *
 * 09/7/2015-2016
 * ...
 *
 * 9/07/2015-2016
 * ...
 *
 * 09/2015-2016
 * ...
 *
 * 9/2015-2016
 * ...
 *
 * 09/07-2016
 * ...
 *
 * 9/7-2016
 * ...
 *
 * 09/7-2016
 * ...
 *
 * 9/07-2016
 * ...
 *
 */

/**
 * Extension to change some protected method to public method for tests
 */
class Extended_For_Tests extends Search_Parameters_Parser
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $search     array user-input search string
	 */
	public function __construct($class_name, $search)
	{
		parent::__construct($class_name, $search);
		$this->currentDateTime = new Date_Time('2016-06-15 12:30:45');
		$this->currentYear     = $this->currentDateTime->format('Y');
		$this->currentMonth    = $this->currentDateTime->format('m');
		$this->currentDay      = $this->currentDateTime->format('d');
		$this->currentHour     = $this->currentDateTime->format('H');
		$this->currentMinutes  = $this->currentDateTime->format('i');
		$this->currentSeconds  = $this->currentDateTime->format('s');
	}

	//----------------------------------------------------------------------- correctDateWildcardExpr
	/**
	 * @param $expr string
	 * @param $part string @values Date_Time::DAY, Date_Time::MONTH, Date_Time::YEAR, Date_Time::HOUR,
	 *              Date_Time::MINUTE, Date_Time::SECOND
	 * @return void
	 */
	public function correctDateWildcardExpr(&$expr, $part)
	{
		parent::correctDateWildcardExpr($expr, $part);
	}

}
