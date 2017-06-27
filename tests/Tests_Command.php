<?php
namespace ITRocks\Framework\Tests;

use ITRocks\Framework\Logger\Text_Output;
use PHPUnit_TextUI_Command;

/**
 * The tests class enables running of unit test
 */
class Tests_Command extends PHPUnit_TextUI_Command
{

	//--------------------------------------------------------------------------------- $configurator
	/**
	 * @var Tests_Configurator
	 */
	public $configurator;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->configurator = Tests_Configurator::get();
	}

	//--------------------------------------------------------------------------- parsePHPUnitOptions
	/**
	 * @param string[] $options array of options key=>value
	 *
	 * @return string[] of options as un command line
	 */
	function parsePHPUnitOptions($options)
	{
		$parsed_options = [];

		foreach ($options as $key => $value) {
			if (isset($key) && !is_numeric($key)) {
				$parsed_options[] = '--' . $key;
			}

			if ($value) {
				$parsed_options[] = $value;
			}
		}

		return $parsed_options;
	}

	//-------------------------------------------------------------------------------------- runTests
	/**
	 * Run the required tests
	 *
	 * @param string[] $query_options Other options passed from query params
	 */
	public function runTests($query_options = [])
	{
		set_time_limit(0);

		$options = $this->parsePHPUnitOptions(
			array_merge($this->configurator->phpunit_options, $query_options)
		);

		$output = new Text_Output();
		$output->log('Running tests with options : ');
		$line = '';
		foreach ($options as $option) {
			if (substr($option, 0, 2) === '--') {
				$output->log(TAB . $line);
				$line = '';
			}
			$line .= SP . $option;
		}
		$output->log(TAB . $line);

		$this->run($options);

		$output->end();
	}

}
