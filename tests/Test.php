<?php
namespace ITRocks\Framework\Tests;

use ITRocks\Framework\Controller\Response;

/**
 * All unit test classes must extend this, to access its begin(), end() and assume() methods
 */
class Test extends Testable
{

	//-------------------------------------------------------------------------------------- $capture
	/**
	 * Capture of the output, filled in by captureStart() and flushed by captureEnd()
	 *
	 * @var string
	 */
	private $capture;

	//----------------------------------------------------------------------------------- $start_time
	/**
	 * The start time of each test
	 *
	 * @var float
	 */
	public $start_time;

	//---------------------------------------------------------------------------------------- assume
	/**
	 * Assumes a checked value is the same than an assumed value
	 *
	 * @param $test        string the name of the test (ie 'Method_Name[.test_name]')
	 * @param $check       mixed the checked value
	 * @param $assume      mixed the assumed value
	 * @param $diff_output boolean set to false in order not to output the diff of check and assume
	 * @return boolean true if the checked value corresponds to the assumed value
	 */
	protected function assume($test, $check, $assume, $diff_output = true)
	{
		$duration = round((microtime(true) - $this->start_time) * 1000000);
		$check  = $this->toArray($check);
		$assume = $this->toArray($assume);
		if (is_array($check) && is_array($assume)) {
			$diff1 = arrayDiffRecursive($check, $assume, true);
			$diff2 = arrayDiffRecursive($assume, $check, true);
			$ok = !$diff1 && !$diff2;
		}
		else {
			$diff1 = $check;
			$diff2 = $assume;
			$ok = ($check === $assume);
		}
		if ($ok) {
			if ($duration > 9999) {
				$duration = round($duration / 1000) . 'ms';
			}
			else {
				$duration .= 'μs';
			}
			$result = '<span style="color:green;font-weight:bold">OK</span> (<i>' . $duration . '</i>)';
			$result_code = Response::OK;
		}
		else {
			$result = '<span style="color:red;font-weight:bold">BAD</span>'
			. '<pre style="color:red;font-weight:bold;">[' . print_r($check, true) . ']</pre>'
			. '<pre style="color:blue;font-weight:bold;">[' . print_r($assume, true) . ']</pre>'
			. (
				($diff_output && $diff1)
				? ('<pre style="color:orange;font-weight:bold;">[' . print_r($diff1, true) . ']</pre>')
				: ''
			)
			. (
				($diff_output && $diff2)
				? ('<pre style="color:orange;font-weight:bold;">[' . print_r($diff2, true) . ']</pre>')
				: ''
			);
			$result_code = Response::ERROR;
		}
		$is_error = ($result_code !== Response::OK);
		if ($this->header && $is_error) {
			echo $this->header;
			$this->header = '';
		}
		if (($this->show === self::ALL) || ($is_error && $this->show === self::ERRORS)) {
			echo '<li>'
				. str_replace(get_class($this) . '::', '', $test) . ' : ' . $result
				. '</li>' . LF;
		}
		if ($is_error) {
			$this->errors_count ++;
		}
		$this->tests_count ++;
		$this->start_time = microtime(true);
		return ($result_code === Response::OK);
	}

	//--------------------------------------------------------------------------------- assumeCapture
	/**
	 * Ends default output capture and assume result
	 *
	 * @param $test   string the name of the test (ie 'Method_Name[.test_name]')
	 * @param $assume string the assumed default output capture result
	 * @return boolean if the checked default output capture string corresponds to the assumed string
	 */
	protected function assumeCapture($test, $assume)
	{
		return $this->assume($test . '.output', $this->captureEnd(), $assume);
	}

	//------------------------------------------------------------------------------------ captureEnd
	/**
	 * Stops capture of the standard output and returns the captured output
	 *
	 * @return string
	 */
	public function captureEnd()
	{
		return $this->capture . ob_get_flush();
	}

	//---------------------------------------------------------------------------------- captureStart
	/**
	 * Start capture of the standard output
	 */
	public function captureStart()
	{
		$test = $this;
		$this->capture = '';
		ob_start(function($buffer) use ($test) {
			$test->capture .= $buffer;
		});
	}

	//--------------------------------------------------------------------------------------- toArray
	/**
	 * @param $array mixed
	 * @return mixed
	 */
	private function toArray($array)
	{
		if (is_object($array)) {
			$array = $this->toArray(get_object_vars($array));
		}
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$array[$key] = $this->toArray($value);
			}
			return $array;
		}
		else {
			return $array;
		}
	}

}
