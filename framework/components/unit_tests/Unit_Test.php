<?php
namespace SAF\Framework\Unit_Tests;
use SAF\Framework\Reflection_Class;

/**
 * All unit test classes must extend this, to access it's begin(), end() and assume() methods
 */
class Unit_Test
{

	//---------------------------------------------------------------------------------------- assume
	/**
	 * @param $test string name of the test
	 * @param $check mixed
	 * @param $assume mixed
	 * @return boolean
	 */
	protected function assume($test, $check, $assume)
	{
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
			$result = "<span style='color:green;font-weight:bold'>OK</span>";
		}
		else {
			$result = "<span style='color:red;font-weight:bold'>BAD</span>"
			. "<pre style='color:red;font-weight:bold;'>[" . print_r($check, true) . "]</pre>"
			. "<pre style='color:blue;font-weight:bold;'>[" . print_r($assume, true) . "]</pre>"
			. ($diff1 ? ("<pre style='color:orange;font-weight:bold;'>[" . print_r($diff1, true) . "]</pre>") : "")
			. ($diff2 ? ("<pre style='color:orange;font-weight:bold;'>[" . print_r($diff2, true) . "]</pre>") : "");
		}
		echo "<li>" . str_replace(get_class($this) . "::", "", $test) . " : " . $result;
		return ($result === "OK");
	}

	//----------------------------------------------------------------------------------------- begin
	public function begin()
	{
		echo "<h3>" . get_class($this) . "</h3>";
		echo "<ul>";
	}

	//------------------------------------------------------------------------------------------- end
	public function end()
	{
		echo "</ul>";
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
