<?php
namespace ITRocks\Framework\Dao\Mysql\File_Logger;

/**
 * Some tools to analyze your script activity with a quick study of queries log
 */
class Performance_Analyzer
{

	//--------------------------------------------------------------------- Long queries measurements
	const LONG      = 0.01;
	const VERY_LONG = 0.1;

	//-------------------------------------------------------------------------------------- $sql_log
	/**
	 * @var string
	 */
	private $sql_log;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $sql_log string A complete SQL log, in Entry::$sql format
	 * @see Entry::$sql
	 */
	public function __construct($sql_log)
	{
		$this->sql_log = $sql_log;
	}

	//------------------------------------------------------------------------------------- parseLine
	/**
	 * @param $line string
	 */
	protected function parseLine($line)
	{
		// TODO HIGH measurements, log long executions
		/*
		if (substr($line, 0, 2) === '# ') {
			if (preg_match('^# \d\d:\d\d:\d\d\.\d\d\d.+$', $line)) {

				// long query : more than 0.01
				// long inter-query work : more than 0.01
				// very long : more than 0.1 (both cases)
			}
		}
		*/
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$start = 0;
		while ($stop = strpos($this->sql_log, LF, $start)) {
			$this->parseLine(substr($this->sql_log, $start, $stop - $start));
			$start = $stop + 1;
		}
	}

}
