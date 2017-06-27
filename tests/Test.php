<?php
namespace ITRocks\Framework\Tests;

/**
 * All unit test classes must extend this, to access its begin(), end() and assume() methods
 */
abstract class Test extends Testable
{

	//-------------------------------------------------------------------------------------- $capture
	/**
	 * Capture of the output, filled in by captureStart() and flushed by captureEnd()
	 *
	 * @var string
	 */
	private $capture;

	//---------------------------------------------------------------------------------------- assume
	/**
	 * Assumes a checked value is the same than an assumed value
	 *
	 * @param $test        string the name of the test (ie 'Method_Name[.test_name]')
	 * @param $check       mixed the checked value
	 * @param $assume      mixed the assumed value
	 */
	protected function assume($test, $check, $assume)
	{
		$check  = $this->toArray($check);
		$assume = $this->toArray($assume);
		$this->assertEquals($assume, $check, $test);
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
		$test          = $this;
		$this->capture = '';
		ob_start(function($buffer) use ($test) {
			$test->capture .= $buffer;
		});
	}

	//--------------------------------------------------------------------------------------- enabled
	/**
	 * Returns true if this test class is enabled, else false.
	 * If false, unit tests will not be executed for this class
	 *
	 * @return boolean
	 */
	public function enabled()
	{
		return true;
	}

	//--------------------------------------------------------------------------------------- toArray
	/**
	 * @param $array   mixed
	 * @param $already object[] objects hash table to avoid recursion
	 * @return mixed
	 */
	private function toArray($array, array $already = [])
	{
		if (is_object($array)) {
			if (isset($already[md5(spl_object_hash($array))])) {
				$array = ['__CLASS__' => get_class($array), '__RECURSE__' => null];
			}
			else {
				$already[md5(spl_object_hash($array))] = true;
				$array = ['__CLASS__' => get_class($array)]
					+ $this->toArray(get_object_vars($array), $already);
			}
		}
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$array[$key] = $this->toArray($value, $already);
			}
			return $array;
		}
		else {
			return $array;
		}
	}

}
