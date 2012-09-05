<?php
namespace SAF\Framework\Tests;

class Unit_Test
{

	//---------------------------------------------------------------------------------------- assume
	protected function assume($test, $check, $assume)
	{
		if (is_array($check) && is_array($assume)) {
			$ok = !array_diff_assoc($check, $assume) && !array_diff_assoc($assume, $check);
		} else {
			$ok = $check === $assume;
		}
		if ($ok) {
			$result = "OK";
		} else {
			$result = "BAD [" . print_r($check, true) . "]";
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
