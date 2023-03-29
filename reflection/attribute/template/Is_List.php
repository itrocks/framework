<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

trait Is_List
{

	//--------------------------------------------------------------------------------------- $values
	public array $values = [];

	//----------------------------------------------------------------------------------- __construct
	public function __construct(mixed ...$values)
	{
		foreach ($values as $value) {
			if (is_string($value) && str_contains($value, ',')) {
				foreach (explode(',', $value) as $value_in) {
					$this->values[] = trim($value_in);
				}
			}
			else {
				$this->values[] = $value;
			}
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return '[' . join(',', $this->values) . ']';
	}

	//------------------------------------------------------------------------------------------- add
	public function add(string $value) : void
	{
		if (in_array($value, $this->values)) {
			return;
		}
		$this->values[] = $value;
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the list has the value into its values
	 */
	public function has(string $value) : bool
	{
		return in_array($value, $this->values);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a value and return true if the value was here and removed, false if the value
	 * was not here
	 */
	public function remove(string $value) : bool
	{
		$key = array_search($value, $this->values, true);
		if ($key) {
			unset($this->values[$key]);
		}
		return $key !== false;
	}

}
