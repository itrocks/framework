<?php
namespace SAF\Framework\Tests;

class Unit_Test
{

	//---------------------------------------------------------------------------------------- assume
	/**
	 * @param string $test name of the test
	 * @param mixed $check
	 * @param mixed $assume
	 * @return boolean
	 */
	protected function assume($test, $check, $assume)
	{
		if (is_array($check) && is_array($assume)) {
			$ok = !array_diff_assoc($check, $assume) && !array_diff_assoc($assume, $check);
		}
		else {
			$ok = ($check === $assume);
		}
		if ($ok) {
			$result = "<span style='color:green;font-weight:bold;'>OK</span>";
		}
		else {
			$result = "BAD"
			. "<pre style='color:red;font-weight:bold;'>[" . print_r($check, true) . "]</pre>"
			. "<pre style='color:blue;font-weight:bold;'>[" . print_r($assume, true) . "]</pre>";
		}
		echo "<li>" . substr($test, strpos($test, "::") + 2) . " : " . $result;
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
}
