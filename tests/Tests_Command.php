<?php
namespace ITRocks\Framework\Tests;

use ITRocks\Framework\Logger\Text_Output;
use PHPUnit\TextUI\Command;
use ReflectionClass;

/**
 * The tests class enables running of unit test
 */
class Tests_Command extends Command
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
	 * @param $options string[] array of options key=>value
	 * @return string[] of options as un command line
	 */
	public function parsePHPUnitOptions(array $options)
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $query_options string[] Other options passed from query params
	 */
	public function runTests(array $query_options = [])
	{
		set_time_limit(0);

		$options = $this->parsePHPUnitOptions(
			array_merge($this->configurator->phpunit_options, $query_options)
		);

		$output = new Text_Output();
		$output->log('Running tests with options : ');
		$line        = '';
		$run_options = [];
		foreach ($options as $option) {
			$run_options[] = $option;
			if (substr($option, 0, 2) === '--') {
				$output->log(TAB . $line);
				$line = '';
			}
			$line .= SP . $option;
			if (
				strlen($option)
				&& ctype_upper($option[0])
				&& ($option !== Tests_Html_ResultPrinter::class)
				&& class_exists($option)
			) {
				/** @noinspection PhpUnhandledExceptionInspection options must be valid class names */
				$file_path = (new ReflectionClass($option))->getFileName();
				if ($file_path) {
					$run_options[] = $file_path;
					$line         .= SP . $file_path;
				}
			}
		}
		$output->log(TAB . $line);

		$this->run($run_options);

		$output->end();
	}

}
